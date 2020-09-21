<?php

namespace suplascripts\models\log;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property User $user
 * @property array $state
 * @property int $channelId
 */
class StateLogEntry extends Model implements BelongsToUser {
    const TABLE_NAME = 'state_logs';
    const USER_ID = 'userId';
    const CHANNEL_ID = 'channelId';
    const STATE = 'state';

    const LOGGED_FUNCTIONS = [
        'LIGHTSWITCH',
        'POWERSWITCH',
        'THERMOMETER',
    ];

    protected $table = self::TABLE_NAME;

    protected $fillable = [self::CHANNEL_ID, self::STATE, self::CREATED_AT];
    protected $jsonEncoded = [self::STATE];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function usesTimestamps() {
        return false;
    }
}
