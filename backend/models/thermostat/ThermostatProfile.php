<?php

namespace suplascripts\models\thermostat;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $name
 * @property string $roomsConfig
 * @property string $activeOn
 * @property string $userId
 */
class ThermostatProfile extends Model
{
    const TABLE_NAME = 'thermostat_profiles';
    const NAME = 'name';
    const ROOMS_CONFIG = 'roomsConfig';
    const ACTIVE_ON = 'activeOn';
    const USER_ID = 'userId';

    protected $fillable = [self::NAME, self::ROOMS_CONFIG, self::ACTIVE_ON];
    protected $jsonEncoded = [self::ROOMS_CONFIG, self::ACTIVE_ON];

    public static function create(array $attributes = [])
    {
        $profile = new self($attributes);
        $profile->save();
        return $profile;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function validate(array $attributes = null): void
    {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::NAME);
        Assertion::notEmptyKey($attributes, self::ROOMS_CONFIG);
    }
}
