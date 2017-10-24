<?php

namespace suplascripts\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\scene\Scene;

/**
 * @property int $id
 * @property string $label
 * @property bool $active
 * @property \DateTime $lastConnectionDate
 */
class Client extends Model {

    const TABLE_NAME = 'clients';
    const LABEL = 'label';
    const ACTIVE = 'active';
    const LAST_CONNECTION_DATE = 'lastConnectionDate';
    const SCENE_ID = 'sceneId';
    const USER_ID = 'userId';

    protected $dates = [self::LAST_CONNECTION_DATE];
    protected $fillable = [self::LABEL, self::ACTIVE];

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
        if (!$this->lastConnectionDate) {
            $this->updateLastConnectionDate();
        }
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function scene(): BelongsTo {
        return $this->belongsTo(Scene::class, self::SCENE_ID);
    }

    public function updateLastConnectionDate() {
        $this->lastConnectionDate = new \DateTime();
    }
}
