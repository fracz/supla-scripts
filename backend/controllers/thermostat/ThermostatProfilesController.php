<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\app\commands\DispatchThermostatCommand;
use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;

class ThermostatProfilesController extends BaseController {

    public function postAction($params) {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        $thermostat = $this->getThermostat($params);
        return $this->getApp()->db->getConnection()->transaction(function () use ($thermostat, $parsedBody) {
            $createdProfile = $thermostat->profiles()->create($parsedBody);
            $thermostat->nextProfileChange = new \DateTime();
            $thermostat->log('Utworzono nowy profil o nazwie ' . $createdProfile->name);
            if ($thermostat->profiles()->count() == 1) {
                $thermostat->log('Termostat został aktywowany.');
                $thermostat->enabled = true;
                $thermostat->save();
                (new DispatchThermostatCommand())->adjust($thermostat);
            }
            $thermostat->save();
            return $this->response($createdProfile)
                ->withStatus(201);
        });
    }

    private function getThermostat($params): Thermostat {
        $this->ensureAuthenticated();
        return $this->ensureExists(Thermostat::where([
            Thermostat::USER_ID => $this->getCurrentUser()->id,
            Thermostat::ID => $params['thermostatId']
        ])->first());
    }

    public function getListAction($params) {
        $thermostat = $this->getThermostat($params);
        $profiles = $thermostat->profiles()->get();
        return $this->response($profiles);
    }

    public function putAction($id) {
        $this->ensureAuthenticated();
        /** @var ThermostatProfile $profile */
        $profile = $this->ensureExists(ThermostatProfile::find($id)->first());
        if ($profile->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $parsedBody = $this->request()->getParsedBody();
        $profile->update($parsedBody);
        $profile->save();
        $profile->thermostat()->first()->log('Wprowadzono zmiany w profilu ' . $profile->name);
        $thermostat = $profile->thermostat()->first();
        $thermostat->nextProfileChange = new \DateTime();
        $thermostat->save();
        return $this->response($profile);
    }

    public function deleteAction($id) {
        $this->ensureAuthenticated();
        $profile = $this->ensureExists(ThermostatProfile::find($id)->first());
        if ($profile->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $profile->thermostat()->first()->log('Usunięto profil ' . $profile->name);
        $profile->delete();
        return $this->response()->withStatus(204);
    }
}
