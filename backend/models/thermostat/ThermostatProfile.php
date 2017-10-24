<?php

namespace suplascripts\models\thermostat;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $name
 * @property mixed $roomsConfig
 * @property array $activeOn
 * @property string $userId
 */
class ThermostatProfile extends Model {

    const TABLE_NAME = 'thermostat_profiles';
    const NAME = 'name';
    const ROOMS_CONFIG = 'roomsConfig';
    const ACTIVE_ON = 'activeOn';
    const USER_ID = 'userId';
    const THERMOSTAT_ID = 'thermostatId';

    protected $fillable = [self::NAME, self::ROOMS_CONFIG, self::ACTIVE_ON];
    protected $jsonEncoded = [self::ROOMS_CONFIG, self::ACTIVE_ON];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function thermostat(): BelongsTo {
        return $this->belongsTo(Thermostat::class, self::THERMOSTAT_ID);
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::NAME);
        Assertion::notEmptyKey($attributes, self::ROOMS_CONFIG, 'You must enter at least one temperature configuration.');
        Assertion::keyExists($attributes, self::ACTIVE_ON);
        Assertion::isArray($attributes[self::ROOMS_CONFIG]);
        Assertion::isArray($attributes[self::ACTIVE_ON]);

        foreach ($attributes[self::ROOMS_CONFIG] as $roomConfig) {
            if (isset($roomConfig['heatTo']) || isset($roomConfig['heatFrom'])) {
                Assertion::notEmptyKey($roomConfig, 'heatTo', 'Heat to value must be provided if heat from is set.');
                Assertion::notEmptyKey($roomConfig, 'heatFrom', 'Heat from value must be provided if heat to is set.');
                Assertion::greaterThan($roomConfig['heatTo'], $roomConfig['heatFrom'], 'Heat from value must be less than heat to value.');
            }
            if (isset($roomConfig['coolTo']) || isset($roomConfig['coolFrom'])) {
                Assertion::notEmptyKey($roomConfig, 'coolTo', 'Cool to value must be provided if cool from is set.');
                Assertion::notEmptyKey($roomConfig, 'coolFrom', 'Cool from value must be provided if cool to is set.');
                Assertion::greaterThan($roomConfig['coolFrom'], $roomConfig['coolTo'], 'Cool to value must be less than heat from value.');
            }
        }
    }
}
