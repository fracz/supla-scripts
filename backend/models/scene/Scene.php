<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;
use suplascripts\models\Model;
use suplascripts\models\User;

/**
 * @property string $slug
 * @property string $label
 * @property string $actions
 * @property string $feedback
 * @property string[] $voiceTriggers
 * @property \DateTime $lastUsed
 * @property User $user
 */
class Scene extends Model {
    const TABLE_NAME = 'scenes';
    const SLUG = 'slug';
    const LABEL = 'label';
    const ACTIONS = 'actions';
    const FEEDBACK = 'feedback';
    const VOICE_TRIGGERS = 'voiceTriggers';
    const LAST_USED = 'lastUsed';
    const USER_ID = 'userId';

    protected $dates = [self::LAST_USED];
    protected $fillable = [self::LABEL, self::ACTIONS, self::FEEDBACK, self::VOICE_TRIGGERS];
    protected $jsonEncoded = [self::VOICE_TRIGGERS];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, self::USER_ID);
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
        Assertion::true(($attributes[self::FEEDBACK] ?? false) || ($attributes[self::ACTIONS] ?? false), 'Scene must have either feedback or actions.');
    }
}
