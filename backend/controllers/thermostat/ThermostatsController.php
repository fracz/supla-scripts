<?php

namespace suplascripts\controllers\thermostat;

use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;

class ThermostatsController extends BaseController
{
    public function getDefaultAction()
    {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::where([Thermostat::USER_ID => $this->getCurrentUser()->id])->first());
        return $this->thermostatResponse($thermostat);
    }

    public function patchAction($id)
    {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::find($id)->first());
        if ($thermostat->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $parsedBody = $this->request()->getParsedBody();
        if (isset($parsedBody['enabled'])) {
            $thermostat->enabled = boolval($parsedBody['enabled']);
        }
        if (array_key_exists('activeProfileId', $parsedBody)) {
            if ($parsedBody['activeProfileId']) {
                $profile = $this->ensureExists(ThermostatProfile::where([ThermostatProfile::USER_ID => $this->getCurrentUser()->id, ThermostatProfile::ID => $parsedBody['activeProfileId']])->first());
                $thermostat->activeProfile()->associate($profile);
            } else {
                $thermostat->activeProfile()->dissociate();
            }
        }
        $thermostat->save();
        return $this->thermostatResponse($thermostat);
    }

    private function thermostatResponse(Thermostat $thermostat)
    {
        $api = new SuplaApi($this->getCurrentUser());
        $channelsToFetch = [];
        $channels = [];
        foreach ($thermostat->rooms()->get() as $room) {
            $channelsToFetch = array_merge($channelsToFetch, $room->thermometers);
            $channelsToFetch = array_merge($channelsToFetch, $room->heaters ?? []);
            $channelsToFetch = array_merge($channelsToFetch, $room->coolers ?? []);
        }
        foreach (array_unique($channelsToFetch) as $channelId) {
            $channels[$channelId] = $api->getChannelWithState($channelId);
        }
        return $this->response([
            'id' => $thermostat->id,
            'enabled' => boolval($thermostat->enabled),
            'profiles' => $thermostat->profiles()->get(),
            'rooms' => $thermostat->rooms()->get(),
            'activeProfile' => $thermostat->activeProfile()->first(),
            'nextProfileChange' => $thermostat->nextProfileChange->format(\DateTime::ATOM),
            'channels' => $channels,
            'roomState' => $thermostat->roomsState,
        ]);
    }
}
