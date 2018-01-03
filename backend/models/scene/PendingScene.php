<?php

namespace suplascripts\models\scene;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;

/**
 * @property string $actions
 * @property \DateTime $executeAfter
 * @property Scene $scene
 */
class PendingScene extends Model {
    const TABLE_NAME = 'pending_scenes';
    const SCENE_ID = 'sceneId';
    const ACTIONS = 'actions';
    const EXECUTE_AFTER = 'executeAfter';

    protected $dates = [self::EXECUTE_AFTER];
    protected $fillable = [self::ACTIONS, self::EXECUTE_AFTER];

    public function scene(): BelongsTo {
        return $this->belongsTo(Scene::class, self::SCENE_ID);
    }
}
