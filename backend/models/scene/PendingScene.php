<?php

namespace suplascripts\models\scene;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use suplascripts\models\Model;

/**
 * @property string $actions
 * @property \DateTime $executeAfter
 * @property Scene $scene
 * @property string[] $sceneStack
 */
class PendingScene extends Model {
    const TABLE_NAME = 'pending_scenes';
    const SCENE_ID = 'sceneId';
    const ACTIONS = 'actions';
    const SCENE_STACK = 'sceneStack';
    const EXECUTE_AFTER = 'executeAfter';

    protected $dates = [self::EXECUTE_AFTER];
    protected $jsonEncoded = [self::SCENE_STACK];
    protected $fillable = [self::ACTIONS, self::EXECUTE_AFTER, self::SCENE_STACK];

    public function scene(): BelongsTo {
        return $this->belongsTo(Scene::class, self::SCENE_ID);
    }
}
