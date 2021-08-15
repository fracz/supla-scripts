<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $slug
 * @property string $label
 * @property string[] $actions
 * @property string $feedback
 * @property string $condition
 * @property string $trigger
 * @property boolean $lastTriggerState
 * @property boolean $triggerChannels
 * @property string[] $voiceTriggers
 * @property \DateTime $lastUsed
 * @property User $user
 * @property string $intervals
 * @property \DateTime $nextExecutionTime
 * @property array $notifications
 * @property boolean $enabled
 */
class Scene extends Model implements BelongsToUser {
    const TABLE_NAME = 'scenes';
    const SLUG = 'slug';
    const LABEL = 'label';
    const ACTIONS = 'actions';
    const FEEDBACK = 'feedback';
    const CONDITION = 'condition';
    const TRIGGER = 'trigger';
    const LAST_TRIGGER_STATE = 'lastTriggerState';
    const TRIGGER_CHANNELS = 'triggerChannels';
    const VOICE_TRIGGERS = 'voiceTriggers';
    const LAST_USED = 'lastUsed';
    const USER_ID = 'userId';
    const INTERVALS = 'intervals';
    const NOTIFICATIONS = 'notifications';
    const NEXT_EXECUTION_TIME = 'nextExecutionTime';
    const ENABLED = 'enabled';

    protected $dates = [self::LAST_USED, self::NEXT_EXECUTION_TIME];
    protected $fillable = [self::LABEL, self::ACTIONS, self::FEEDBACK, self::VOICE_TRIGGERS, self::CONDITION, self::TRIGGER, self::INTERVALS,
        self::NOTIFICATIONS, self::ENABLED];
    protected $jsonEncoded = [self::ACTIONS, self::VOICE_TRIGGERS, self::TRIGGER_CHANNELS, self::NOTIFICATIONS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function pendingScenes(): HasMany {
        return $this->hasMany(PendingScene::class, PendingScene::SCENE_ID);
    }

    public function log($data) {
        $this->user->log('scene', $data, $this->id);
    }

    public function generateSlug() {
        if (!$this->slug) {
            $this->slug = Uuid::getFactory()->uuid4();
        }
    }

    public function clearSlug() {
        $this->slug = null;
    }

    public function save(array $options = []) {
        if (!$this->voiceTriggers) {
            $this->voiceTriggers = [];
        }
        $this->voiceTriggers = array_values(array_unique(array_map(function ($trigger) {
            return trim(mb_strtolower($trigger, 'UTF-8'));
        }, $this->voiceTriggers)));
        if ($this->trigger) {
            $feedbackInterpolator = new FeedbackInterpolator($this);
            $this->lastTriggerState = boolval($feedbackInterpolator->interpolate($this->trigger, true));
            $this->triggerChannels = $feedbackInterpolator->getUsedChannelsIds($this->trigger);
        } else {
            $this->triggerChannels = [];
        }
        if ($this->getIntervals()) {
            $this->updateNextExecutionTime();
        } else {
            $this->nextExecutionTime = null;
        }
        return parent::save($options);
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::keyExists($attributes, self::LABEL, 'Scene must have a label.');
        Assertion::notBlank($attributes[self::LABEL], 'Scene must have a label.');
        $actions = array_filter($attributes[self::ACTIONS] ?? []);
        $notifications = array_filter($attributes[self::NOTIFICATIONS] ?? []);
        Assertion::true(
            ($attributes[self::FEEDBACK] ?? false) || $actions || $notifications,
            'Scene must have either feedback, actions or notifications.'
        );
        if ($attributes[self::ACTIONS]) {
            $actions = $attributes[self::ACTIONS];
            Assertion::isArray($actions);
            Assertion::allNumeric(array_keys($actions));
            Assertion::allGreaterOrEqualThan(array_keys($actions), 0);
        }
        $intervals = $this->getIntervals($attributes[self::INTERVALS]);
        foreach ($intervals as $interval) {
            Assertion::true(CronExpression::isValidExpression($interval), 'Invalid interval: ' . $interval);
            $cron = CronExpression::factory($interval);
            $time = time();
            try {
                $nextTimestamp = $cron->getNextRunDate(new \DateTime('now', $this->user->getTimezone()))->getTimestamp();
            } catch (\RuntimeException $e) {
                Assertion::false(true, 'Invalid interval: ' . $interval);
            }
            Assertion::greaterOrEqualThan($nextTimestamp, $time);
        }
        if ($notifications) {
            Assertion::isArray($notifications);
            foreach ($notifications as $notification) {
                Assertion::notEmptyKey($notification, 'message', 'Every notification must have a message.');
                Assertion::lessOrEqualThan(count($notification), 3, 'Invalid notification config.');
            }
        }
    }

    private function getIntervals(string $fromIntervals = null) {
        $intervals = $fromIntervals ?: $this->intervals;
        return array_filter(array_map('trim', explode('|', $intervals)));
    }

    public function calculateNextExecutionTime(): int {
        if (preg_match('#\s*\*/?(\d+)? \* \* \* \* ?\*?\s*#', $this->intervals, $matches)) {
            $nextExecutionTime = time() + max(1, ($matches[1] ?? 1)) * 60;
        } else {
            $nextRunDates = array_map(function ($cronExpression) {
                $cron = CronExpression::factory($cronExpression);
                return $cron->getNextRunDate(new \DateTime('now', $this->user->getTimezone()))->getTimestamp();
            }, $this->getIntervals());
            $nextExecutionTime = min($nextRunDates);
        }
        return max($nextExecutionTime, time() + 600);
    }

    public function updateNextExecutionTime() {
        $this->nextExecutionTime = new \DateTime('@' . $this->calculateNextExecutionTime());
    }
}
