<?php

namespace suplascripts\models\thermostat;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $label
 * @property bool $enabled
 * @property string $slug
 * @property mixed $roomsState
 * @property mixed $devicesState
 * @property \DateTime $nextProfileChange
 */
class Thermostat extends Model
{
    const TABLE_NAME = 'thermostats';
    const LABEL = 'label';
    const ENABLED = 'enabled';
    const ROOMS_STATE = 'roomsState';
    const SLUG = 'slug';
    const DEVICES_STATE = 'devicesState';
    const NEXT_PROFILE_CHANGE = 'nextProfileChange';
    const ACTIVE_PROFILE_ID = 'activeProfileId';
    const USER_ID = 'userId';

    protected $dates = [self::NEXT_PROFILE_CHANGE];
    protected $fillable = [self::LABEL, self::ENABLED];
    protected $jsonEncoded = [self::ROOMS_STATE, self::DEVICES_STATE];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(ThermostatRoom::class, ThermostatRoom::THERMOSTAT_ID);
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(ThermostatProfile::class, ThermostatProfile::THERMOSTAT_ID);
    }

    public function activeProfile(): BelongsTo
    {
        return $this->belongsTo(ThermostatProfile::class, self::ACTIVE_PROFILE_ID);
    }

    public function save(array $options = [])
    {
        if (!$this->exists) {
            $this->roomsState = [];
            $this->devicesState = [];
            $this->slug = Uuid::getFactory()->uuid4();
        }
        return parent::save($options);
    }

    public function log($data)
    {
        $this->user()->first()->log('thermostat', "[$this->label] " . $data);
    }
}
