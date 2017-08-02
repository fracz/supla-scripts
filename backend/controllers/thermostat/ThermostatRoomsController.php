<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatRoom;

class ThermostatRoomsController extends BaseController
{
    public function postAction()
    {
        $parsedBody = $this->request()->getParsedBody();
        return $this->getApp()->db->getConnection()->transaction(function () use ($parsedBody) {
            /** @var Thermostat $thermostat */
            $thermostat = Thermostat::firstOrCreate([ThermostatRoom::USER_ID => $this->getCurrentUser()->id]);
            $createdRoom = $thermostat->rooms()->create($parsedBody);
            $createdRoom->save();
            return $this->response($createdRoom)
                ->withStatus(201);
        });
    }

    public function getListAction()
    {
        $rooms = ThermostatRoom::where([ThermostatRoom::USER_ID => $this->getCurrentUser()->id])->get();
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
        return $this->response($room);
    }

    public function deleteAction($id)
    {
        $room = $this->ensureExists(ThermostatRoom::find($id)->first());
        if ($room->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $room->delete();
        return $this->response()->withStatus(204);
    }
}
