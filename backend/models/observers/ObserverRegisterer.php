<?php

namespace suplascripts\models\observers;

use suplascripts\models\Client;
use suplascripts\models\scene\Scene;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatRoom;
use suplascripts\models\User;

class ObserverRegisterer {

    public static function registerModelObservers() {
        User::observe(ModelValidator::class);
        Thermostat::observe(UserIdSetter::class);
        ThermostatRoom::observe(ModelValidator::class);
        ThermostatRoom::observe(UserIdSetter::class);
        ThermostatProfile::observe(UserIdSetter::class);
        ThermostatProfile::observe(ModelValidator::class);
        Client::observe(UserIdSetter::class);
        Scene::observe(UserIdSetter::class);
        Scene::observe(ModelValidator::class);
    }
}
