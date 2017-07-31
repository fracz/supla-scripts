<?php

namespace suplascripts\models\observers;

use suplascripts\models\thermostat\ThermostatRoom;
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
