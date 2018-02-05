<?php

namespace suplascripts\models\notification;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $label
 * @property array $config
 * @property User $user
 */
class Notification extends Model {
    const TABLE_NAME = 'notifications';
    const LABEL = 'label';
    const CONFIG = 'config';
    const USER_ID = 'userId';

    protected $fillable = [self::LABEL, self::CONFIG];
    protected $jsonEncoded = [self::CONFIG];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function log($data) {
        $this->user->log('notification', $data, $this->id);
    }
}
