<?php

namespace suplascripts\models\voice;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $label
 * @property bool $enabled
 * @property string $slug
 * @property mixed $roomsState
 * @property mixed $devicesState
 * @property \DateTime $nextProfileChange
 * @property User $user
 */
class VoiceCommand extends Model
{
    const TABLE_NAME = 'voice';
    const TRIGGERS = 'triggers';
    const ACTIONS = 'actions';
    const FEEDBACK = 'feedback';
    const LAST_USED = 'lastUsed';
    const USER_ID = 'userId';

    protected $dates = [self::LAST_USED];
    protected $fillable = [self::TRIGGERS, self::ACTIONS, self::FEEDBACK];
    protected $jsonEncoded = [self::TRIGGERS];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function log($data)
    {
        $this->user()->first()->log('voice', $data, $this->id);
    }
}
