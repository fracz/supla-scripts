<?php

namespace suplascripts\models\scene;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $slug
 * @property string $label
 * @property int $ordinalNumber
 */
class SceneGroup extends Model implements BelongsToUser {
    const TABLE_NAME = 'scene_groups';
    const LABEL = 'label';
    const ORDINAL_NUMBER = 'ordinalNumber';
    const USER_ID = 'userId';

    protected $fillable = [self::LABEL, self::ORDINAL_NUMBER];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function scenes(): HasMany {
        return $this->hasMany(Scene::class, Scene::ID);
    }
}
