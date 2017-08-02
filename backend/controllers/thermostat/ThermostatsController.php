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

    public function getBySlugAction($params)
    {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::where([Thermostat::SLUG => $params['slug']])->first());
        return $this->thermostatResponse($thermostat);
    }

    public function patchAction($params)
    {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::find($params['id']));
        if ((!$this->getCurrentUser() && $thermostat->slug != $params['slug'])
            || ($this->getCurrentUser() && $thermostat->userId != $this->getCurrentUser()->id)) {
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
        if (isset($parsedBody['roomAction'])) {
            $roomId = $parsedBody['roomAction']['roomId'] ?? '';
            $room = new ThermostatRoomConfig([], $thermostat->roomsState[$roomId] ?? []);
            if (isset($parsedBody['roomAction']['clear'])) {
                $room->clearForcedAction();
            } else {
                $room->forceAction($parsedBody['roomAction']['action'], 30);
            }
            $room->updateState($thermostat, $parsedBody['roomAction']['roomId']);
        }
        $thermostat->save();
        return $this->thermostatResponse($thermostat);
    }

    private function thermostatResponse(Thermostat $thermostat)
    {
        $api = new SuplaApi($thermostat->user()->first());
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
            'slug' => $thermostat->slug,
        ]);
    }
}
