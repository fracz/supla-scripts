<?php

namespace suplascripts\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \DateTime $lastConnectionDate
 */
class EventFeedClient extends Model {
    const TABLE_NAME = 'event_feed_clients';
    const LAST_CONNECTION_DATE = 'lastConnectionDate';
    const LAST_STATE = 'lastState';
    const USER_ID = 'userId';
    const ACCESS_ID_ID = 'accessIdId';
    const ACCESS_ID_PASSWORD = 'accessIdPassword';

    protected $dates = [self::LAST_CONNECTION_DATE];
    protected $fillable = [self::LABEL, self::ACTIVE];
    protected $encrypted = [self::ACCESS_ID_PASSWORD];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }
}
