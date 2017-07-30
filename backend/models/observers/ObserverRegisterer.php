<?php

namespace suplascripts\models\observers;

use suplascripts\models\User;

class ObserverRegisterer
{
    public static function registerModelObservers()
    {
        User::observe(ModelValidator::class);
    }
}
