<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\models\ThermostatRoom;

class ThermostatRoomsController extends BaseController
{
    public function postAction()
    {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        return $this->getApp()->db->getConnection()->transaction(function () use ($parsedBody) {
            $createdRoom = ThermostatRoom::create($parsedBody);
            return $this->response($createdRoom)
                ->withStatus(201);
        });
    }
}
