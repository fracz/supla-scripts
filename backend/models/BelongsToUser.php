<?php

namespace suplascripts\models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property User $user
 */
interface BelongsToUser {
    public function user(): BelongsTo;
}
