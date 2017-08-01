<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatRoom;

class ThermostatsController extends BaseController
{
    public function getDefaultAction()
    {
        $this->ensureAuthenticated();
        $profiles = ThermostatProfile::where([ThermostatProfile::USER_ID => $this->getCurrentUser()->id])->get();
        $rooms = ThermostatRoom::where([ThermostatRoom::USER_ID => $this->getCurrentUser()->id])->get();
        $api = new SuplaApi($this->getCurrentUser());
        $channelsToFetch = [];
        $channels = [];
        foreach ($rooms as $room) {
            $channelsToFetch = array_merge($channelsToFetch, $room->thermometers);
            $channelsToFetch = array_merge($channelsToFetch, $room->heaters ?? []);
            $channelsToFetch = array_merge($channelsToFetch, $room->coolers ?? []);
        }
        foreach (array_unique($channelsToFetch) as $channelId) {
            $channels[$channelId] = $api->getChannelWithState($channelId);
        }
        return $this->response([
            'profiles' => $profiles,
            'rooms' => $rooms,
            'channels' => $channels,
            'roomState' => [
                '496b7e75-914b-4bcd-8e21-fe3b030e7fbe' => [
                    'cooling' => true,
                    'target' => 25
                ]
            ]
        ]);
    }
}
