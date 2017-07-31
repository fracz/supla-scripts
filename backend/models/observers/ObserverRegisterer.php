<?php

namespace suplascripts\models\observers;

use suplascripts\models\ThermostatRoom;
use suplascripts\models\User;

class ObserverRegisterer
{
    public static function registerModelObservers()
    {
        User::observe(ModelValidator::class);
        ThermostatRoom::observe(ModelValidator::class);
        ThermostatRoom::observe(UserIdSetter::class);
    }
}
