<?php

namespace suplascripts\models\notification;

use Assert\Assertion;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\User;

/**
 * @property string $label
 * @property string $condition
 * @property string $header
 * @property string $message
 * @property string $speech
 * @property string $intervals
 * @property array $actions
 * @property int $retryInterval
 * @property int $minConditions
 * @property User $user
 * @property string[] $clientIds
 * @property bool $displayIfDisconnected
 * @property bool $onlyInTime
 */
class Notification extends Model implements BelongsToUser {
    const TABLE_NAME = 'notifications';
    const LABEL = 'label';
    const CONDITION = 'condition';
    const MIN_CONDITIONS = 'minConditions';
    const INTERVALS = 'intervals';
    const RETRY_INTERVAL = 'retryInterval';
    const HEADER = 'header';
    const MESSAGE = 'message';
    const ICON = 'icon';
    const SOUND = 'sound';
    const VIBRATE = 'vibrate';
    const FLASH = 'flash';
    const CANCELLABLE = 'cancellable';
    const ONGOING = 'ongoing';
    const AWAKE = 'awake';
    const ACTIONS = 'actions';
    const USER_ID = 'userId';
    const CLIENT_IDS = 'clientIds';
    const SPEECH = 'speech';
    const DISPLAY_IF_DISCONNECTED = 'displayIfDisconnected';
    const ONLY_IN_TIME = 'onlyInTime';

    protected $fillable = [self::LABEL, self::CONDITION, self::INTERVALS, self::HEADER, self::MESSAGE, self::SOUND, self::VIBRATE, self::FLASH,
        self::CANCELLABLE, self::ONGOING, self::AWAKE, self::ACTIONS, self::RETRY_INTERVAL, self::ICON, self::CLIENT_IDS,
        self::SPEECH, self::DISPLAY_IF_DISCONNECTED, self::ONLY_IN_TIME];
    protected $jsonEncoded = [self::ACTIONS, self::CLIENT_IDS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function log($data) {
        $this->user->log('notification', $data, $this->id);
    }

    public function save(array $options = []) {
        $this->minConditions = 1; // backward compatibility
        if (!$this->message) {
            $this->message = '';
        }
        if (!$this->header) {
            $this->header = '';
        }
        if (!$this->condition) {
            $this->condition = '';
        }
        return parent::save($options);
    }

    private function getIntervals(string $fromIntervals = null) {
        $intervals = $fromIntervals ?: $this->intervals;
        return array_map('trim', explode('|', $intervals));
    }

    public function calculateNextNotificationTime($retry = false): int {
        if ($retry && $this->condition && !$this->onlyInTime) {
            return time() + $this->retryInterval;
        } elseif (preg_match('#\s*\*/?(\d+)? \* \* \* \* ?\*?\s*#', $this->intervals, $matches)) {
            return time() + max(1, ($matches[1] ?? 1)) * 60;
        } else {
            $nextRunDates = array_map(function ($cronExpression) {
                $cron = CronExpression::factory($cronExpression);
                return $cron->getNextRunDate(new \DateTime('now', $this->user->getTimezone()))->getTimestamp();
            }, $this->getIntervals());
            return min($nextRunDates);
        }
    }

    public function isConditionMet(): bool {
        if (!trim($this->condition) || !$this->minConditions) {
            return true;
        }
        $condition = (new FeedbackInterpolator($this))->interpolate($this->condition);
        if (strpos($condition, FeedbackInterpolator::NOT_CONNECTED_RESPONSE) !== false) {
            return $this->displayIfDisconnected;
        }
        $conditions = array_map('boolval', array_map('trim', explode(' ', $condition)));
        return count(array_filter($conditions)) >= $this->minConditions;
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::keyExists($attributes, self::INTERVALS);
        Assertion::keyExists($attributes, self::MIN_CONDITIONS);
        Assertion::keyExists($attributes, self::ACTIONS);
        Assertion::isArray($attributes[self::ACTIONS]);
        Assertion::keyExists($attributes, self::CLIENT_IDS, 'Notification should be shown on at least one device.');
        Assertion::isArray($attributes[self::CLIENT_IDS]);
        Assertion::notEmpty($attributes[self::CLIENT_IDS], 'Notification should be shown on at least one device.');
        Assertion::greaterOrEqualThan($attributes[self::MIN_CONDITIONS], 0, 'minConditions must be greater than or equal 0');
        $intervals = array_filter($this->getIntervals($attributes[self::INTERVALS]));
        Assertion::notEmpty($intervals);
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
        foreach ($attributes[self::ACTIONS] as $action) {
            Assertion::isArray($action);
            Assertion::keyExists($action, 'label');
            Assertion::notBlank($action['label']);
        }
    }
}
