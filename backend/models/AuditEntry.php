<?php

namespace suplascripts\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 */
class AuditEntry extends Model {
    const TABLE_NAME = 'audit';
    const USER_ID = 'userId';
    const CHANNEL_ID = 'channelId';
    const NEW_STATE = 'newState';

    protected $table = self::TABLE_NAME;

    protected $fillable = [self::CHANNEL_ID, self::NEW_STATE];
    protected $jsonEncoded = [self::NEW_STATE];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function usesTimestamps() {
        return false;
    }
}
