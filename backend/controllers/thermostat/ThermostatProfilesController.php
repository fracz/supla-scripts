<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;

class ThermostatProfilesController extends BaseController
{
    public function postAction()
    {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        return $this->getApp()->db->getConnection()->transaction(function () use ($parsedBody) {
            /** @var Thermostat $thermostat */
            $thermostat = Thermostat::firstOrCreate([Thermostat::USER_ID => $this->getCurrentUser()->id]);
            $createdProfile = $thermostat->profiles()->create($parsedBody);
            $thermostat->nextProfileChange = new \DateTime();
            $thermostat->save();
            return $this->response($createdProfile)
                ->withStatus(201);
        });
    }

    public function getListAction()
    {
        $this->ensureAuthenticated();
        $profiles = ThermostatProfile::where([ThermostatProfile::USER_ID => $this->getCurrentUser()->id])->get();
        return $this->response($profiles);
    }

    public function putAction($id)
    {
        $this->ensureAuthenticated();
        /** @var ThermostatProfile $profile */
        $profile = $this->ensureExists(ThermostatProfile::find($id)->first());
        if ($profile->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $parsedBody = $this->request()->getParsedBody();
        $profile->update($parsedBody);
        $profile->save();
        $thermostat = $profile->thermostat()->first();
        $thermostat->nextProfileChange = new \DateTime();
        $thermostat->save();
        return $this->response($profile);
    }

    public function deleteAction($id)
    {
        $this->ensureAuthenticated();
        $profile = $this->ensureExists(ThermostatProfile::find($id)->first());
        if ($profile->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $profile->delete();
        return $this->response()->withStatus(204);
    }
}
