<?php

namespace suplascripts\models\thermostat;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use suplascripts\models\Model;

/**
 * @property bool $enabled
 * @property mixed $roomsState
 * @property mixed $devicesState
 * @property \DateTime $nextProfileChange
 */
class Thermostat extends Model
{
    const TABLE_NAME = 'thermostats';
    const ENABLED = 'enabled';
    const ROOMS_STATE = 'roomsState';
    const DEVICES_STATE = 'devicesState';
    const NEXT_PROFILE_CHANGE = 'nextProfileChange';
    const ACTIVE_PROFILE_ID = 'activeProfileId';
    const USER_ID = 'userId';

    protected $dates = [self::NEXT_PROFILE_CHANGE];
    protected $fillable = [self::ENABLED];
    protected $jsonEncoded = [self::ROOMS_STATE, self::DEVICES_STATE];

    public static function create()
    {
        $thermostat = new self();
        $thermostat->save();
        return $thermostat;
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


}
