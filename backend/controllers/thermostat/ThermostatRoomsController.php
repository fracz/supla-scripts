<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatRoom;

class ThermostatRoomsController extends BaseController
{
    public function postAction($id)
    {
        $thermostat = $this->getThermostat($id);
        $parsedBody = $this->request()->getParsedBody();
        return $this->getApp()->db->getConnection()->transaction(function () use ($thermostat, $parsedBody) {
            $createdRoom = $thermostat->rooms()->create($parsedBody);
            $createdRoom->save();
            $thermostat->log('Utworzono pomieszczenie ' . $createdRoom->name);
            return $this->response($createdRoom)
                ->withStatus(201);
        });
    }

    private function getThermostat($params): Thermostat
    {
        $this->ensureAuthenticated();
        return $this->ensureExists(Thermostat::where([
            Thermostat::USER_ID => $this->getCurrentUser()->id,
            Thermostat::ID => $params['thermostatId']
        ])->first());
    }

    public function getListAction($params)
    {
        $thermostat = $this->getThermostat($params);
        $rooms = $thermostat->rooms()->get();
        return $this->response($rooms);
    }

    public function putAction($id)
    {
        $room = $this->ensureExists(ThermostatRoom::find($id)->first());
        if ($room->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $parsedBody = array_merge(['heaters' => [], 'coolers' => []], $this->request()->getParsedBody());
        $room->update($parsedBody);
        $room->save();
        $room->thermostat()->first()->log('Zmieniono pomieszczenie ' . $room->name);
        return $this->response($room);
    }

    public function deleteAction($id)
    {
        $room = $this->ensureExists(ThermostatRoom::find($id)->first());
        if ($room->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $room->thermostat()->first()->log('Usunięto pomieszczenie ' . $room->name);
        $room->delete();
        return $this->response()->withStatus(204);
    }
}
