<?php

namespace suplascripts\models\thermostat;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $label
 * @property bool $enabled
 * @property string $slug
 * @property string $target
 * @property mixed $roomsState
 * @property mixed $devicesState
 * @property \DateTime $nextProfileChange
 * @property User $user
 */
class Thermostat extends Model implements BelongsToUser {

    const TABLE_NAME = 'thermostats';
    const LABEL = 'label';
    const ENABLED = 'enabled';
    const ROOMS_STATE = 'roomsState';
    const SLUG = 'slug';
    const DEVICES_STATE = 'devicesState';
    const NEXT_PROFILE_CHANGE = 'nextProfileChange';
    const ACTIVE_PROFILE_ID = 'activeProfileId';
    const USER_ID = 'userId';
    const TARGET = 'target';

    protected $dates = [self::NEXT_PROFILE_CHANGE];
    protected $fillable = [self::LABEL, self::ENABLED];
    protected $jsonEncoded = [self::ROOMS_STATE, self::DEVICES_STATE];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function rooms(): HasMany {
        return $this->hasMany(ThermostatRoom::class, ThermostatRoom::THERMOSTAT_ID);
    }

    public function profiles(): HasMany {
        return $this->hasMany(ThermostatProfile::class, ThermostatProfile::THERMOSTAT_ID);
    }

    public function activeProfile(): BelongsTo {
        return $this->belongsTo(ThermostatProfile::class, self::ACTIVE_PROFILE_ID);
    }

    public function save(array $options = []) {
        if (!$this->exists) {
            $this->roomsState = [];
            $this->devicesState = [];
            $this->slug = Uuid::getFactory()->uuid4();
        }
        return parent::save($options);
    }

    public function log($data) {
        $this->user()->first()->log('thermostat', $data, $this->id);
    }

    public function shouldChangeProfile(\DateTime $now = null): bool {
        if (!$now) {
            $now = new \DateTime();
        }
        return $now >= $this->nextProfileChange;
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::LABEL);
        Assertion::notEmptyKey($attributes, self::TARGET);
        Assertion::inArray($attributes[self::TARGET], ['temperature', 'humidity']);
    }
}
