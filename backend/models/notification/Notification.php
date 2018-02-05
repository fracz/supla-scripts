<?php

namespace suplascripts\models\notification;

use Assert\Assertion;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $label
 * @property array $intervals
 * @property User $user
 */
class Notification extends Model {
    const TABLE_NAME = 'notifications';
    const LABEL = 'label';
    const CONDITION = 'condition';
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

    protected $fillable = [self::LABEL, self::CONDITION, self::INTERVALS, self::HEADER, self::MESSAGE, self::SOUND, self::VIBRATE, self::FLASH,
        self::CANCELLABLE, self::ONGOING, self::AWAKE, self::ACTIONS, self::RETRY_INTERVAL];
    protected $jsonEncoded = [self::ACTIONS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function log($data) {
        $this->user->log('notification', $data, $this->id);
    }

    public function save(array $options = []) {
        if (!$this->intervals) {
            $this->intervals = '*/15 * * * *';
        }
        return parent::save($options);
    }

    public function getIntervals(string $fromIntervals = null) {
        $intervals = $fromIntervals ?: $this->intervals;
        return array_map('trim', explode(',', $intervals));
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        if (isset($attributes[self::INTERVALS]) && $attributes[self::INTERVALS]) {
            $intervals = $this->getIntervals($attributes[self::INTERVALS]);
            Assertion::notEmpty($intervals);
            foreach ($intervals as $interval) {
                Assertion::true(CronExpression::isValidExpression($interval), 'Invalid interval: ' . $interval);
            }
        }
    }
}
