<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;
use suplascripts\models\BelongsToUser;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $slug
 * @property string $label
 * @property string[] $actions
 * @property string $feedback
 * @property string $condition
 * @property string[] $voiceTriggers
 * @property \DateTime $lastUsed
 * @property User $user
 */
class Scene extends Model implements BelongsToUser {
    const TABLE_NAME = 'scenes';
    const SLUG = 'slug';
    const LABEL = 'label';
    const ACTIONS = 'actions';
    const FEEDBACK = 'feedback';
    const CONDITION = 'condition';
    const VOICE_TRIGGERS = 'voiceTriggers';
    const LAST_USED = 'lastUsed';
    const USER_ID = 'userId';

    protected $dates = [self::LAST_USED];
    protected $fillable = [self::LABEL, self::ACTIONS, self::FEEDBACK, self::VOICE_TRIGGERS, self::CONDITION];
    protected $jsonEncoded = [self::ACTIONS, self::VOICE_TRIGGERS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
    }

    public function pendingScenes(): HasMany {
        return $this->hasMany(PendingScene::class, PendingScene::SCENE_ID);
    }

    public function log($data) {
        $this->user->log('scene', $data, $this->id);
    }

    public function generateSlug() {
        if (!$this->slug) {
            $this->slug = Uuid::getFactory()->uuid4();
        }
    }

    public function clearSlug() {
        $this->slug = null;
    }

    public function save(array $options = []) {
        if (!$this->voiceTriggers) {
            $this->voiceTriggers = [];
        }
        $this->voiceTriggers = array_values(array_unique(array_map(function ($trigger) {
            return trim(mb_strtolower($trigger, 'UTF-8'));
        }, $this->voiceTriggers)));
        return parent::save($options);
    }

    public function validate(array $attributes = null) {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::keyExists($attributes, self::LABEL, 'Scene must have a label.');
        Assertion::notBlank($attributes[self::LABEL], 'Scene must have a label.');
        $actions = array_filter($attributes[self::ACTIONS] ?? []);
        Assertion::true(
            ($attributes[self::FEEDBACK] ?? false) || $actions,
            'Scene must have either feedback or actions.'
        );
        if ($attributes[self::ACTIONS]) {
            $actions = $attributes[self::ACTIONS];
            Assertion::isArray($actions);
            Assertion::allNumeric(array_keys($actions));
            Assertion::allGreaterOrEqualThan(array_keys($actions), 0);
        }
    }
}
