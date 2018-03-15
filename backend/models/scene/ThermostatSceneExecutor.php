<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use suplascripts\app\commands\DispatchThermostatCommand;
use suplascripts\models\HasApp;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\User;

class ThermostatSceneExecutor {
    use HasApp;

    public function setThermostatProfile($thermostatId, $profileId, User $user = null) {
        /** @var Thermostat $thermostat */
        $thermostat = Thermostat::find($thermostatId);
        if ($thermostat) {
            $user = $user ? $user : $this->getApp()->getCurrentUser();
            Assertion::eq($user->id, $thermostat->userId);
            $profile = $thermostat->profiles()->find([ThermostatProfile::ID => $profileId])->first();
            if ($profile || !boolval($profileId) || $profileId == 'false') {
                if ($profile) {
                    $thermostat->activeProfile()->associate($profile);
                    $thermostat->log('Manualnie ustawiono profil (sceną) na ' . $profile->name);
                } else {
                    $thermostat->activeProfile()->dissociate();
                    $thermostat->log('Manualnie wyłączono profil (sceną).');
                }
                $thermostat->save();
                $command = new DispatchThermostatCommand();
                $command->adjust($thermostat);
                return true;
            }
        }
        return false;
    }
}
