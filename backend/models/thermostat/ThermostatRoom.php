<?php

namespace suplascripts\models\thermostat;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\User;

/**
 * @property string $name
 * @property int[] $thermometers
 * @property int[] $heaters
 * @property int[] $coolers
 * @property string $userId
 * @property Thermostat $thermostat
 */
class ThermostatRoom extends Model {

    const TABLE_NAME = 'thermostat_rooms';
    const NAME = 'name';
    const THERMOMETERS = 'thermometers';
    const HEATERS = 'heaters';
    const COOLERS = 'coolers';
    const USER_ID = 'userId';
    const THERMOSTAT_ID = 'thermostatId';

    protected $fillable = [self::NAME, self::THERMOMETERS, self::HEATERS, self::COOLERS];
    protected $jsonEncoded = [self::THERMOMETERS, self::HEATERS, self::COOLERS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function thermostat(): BelongsTo {
        return $this->belongsTo(Thermostat::class, self::THERMOSTAT_ID);
    }

    public function getCurrentTargetValue(): float {
        $api = SuplaApi::getInstance($this->user()->first());
        $field = $this->thermostat->target;
        $temperatures = array_map(function ($channelId) use ($field, $api) {
            return $api->getChannelWithState($channelId)->state->{$field} ?? 0;
        }, $this->thermometers);
        $temperatures = array_filter($temperatures);
        return array_sum($temperatures) / (count($temperatures) ?: 1);
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::NAME);
        Assertion::notEmptyKey($attributes, self::THERMOMETERS, 'Room has to have at least one thermometer.');
        Assertion::keyExists($attributes, self::HEATERS);
        Assertion::keyExists($attributes, self::COOLERS);
        Assertion::greaterThan(count($attributes[self::HEATERS]) + count($attributes[self::COOLERS]), 0, 'You need to define at least one device!');
    }
}
