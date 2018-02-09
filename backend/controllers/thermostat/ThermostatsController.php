<?php

namespace suplascripts\controllers\thermostat;

use Assert\Assertion;
use suplascripts\app\commands\DispatchThermostatCommand;
use suplascripts\controllers\BaseController;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\supla\SuplaApiException;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatRoom;
use suplascripts\models\thermostat\ThermostatRoomConfig;

class ThermostatsController extends BaseController {

    public function getAction($params) {
        $this->ensureAuthenticated();
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists($this->getCurrentUser()->thermostats()->getQuery()->find($params)->first());
        if ($this->request()->getParam('simple', false)) {
            return $this->response($thermostat);
        } else {
            return $this->thermostatResponse($thermostat);
        }
    }

    public function getListAction() {
        $this->ensureAuthenticated();
        $thermostats = Thermostat::where([Thermostat::USER_ID => $this->getCurrentUser()->id])->orderBy(Thermostat::LABEL)->get();
        return $this->response($thermostats);
    }

    public function postAction() {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        /** @var Thermostat $thermostat */
        $thermostat = $this->getCurrentUser()->thermostats()->create([Thermostat::LABEL => $parsedBody['label']]);
        $thermostat->target = $parsedBody['target'];
        $thermostat->save();
        $thermostat->log('Utworzono termostat');
        return $this->response($thermostat)->withStatus(201);
    }

    public function getBySlugAction($params) {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::where([Thermostat::SLUG => $params['slug']])->first());
        return $this->thermostatResponse($thermostat);
    }

    public function patchAction($params) {
        /** @var Thermostat $thermostat */
        $thermostat = $this->ensureExists(Thermostat::find($params['id']));
        if ((!$this->getCurrentUser() && $thermostat->slug != $params['slug'])
            || ($this->getCurrentUser() && $thermostat->userId != $this->getCurrentUser()->id)) {
            throw new Http403Exception();
        }
        $parsedBody = $this->request()->getParsedBody();
        if (isset($parsedBody['enabled'])) {
            $thermostat->enabled = boolval($parsedBody['enabled']);
            $thermostat->log(($thermostat->enabled ? 'Włączono' : 'Wyłączono') . ' termostat.');
        }
        if (array_key_exists('activeProfileId', $parsedBody)) {
            if ($parsedBody['activeProfileId']) {
                $profile = $this->ensureExists($thermostat->profiles()->find([ThermostatProfile::ID => $parsedBody['activeProfileId']])->first());
                $thermostat->activeProfile()->associate($profile);
                $thermostat->log('Manualnie ustawiono profil na ' . $profile->name);
            } else {
                $thermostat->log('Manualnie wyłączono profil.');
                $thermostat->activeProfile()->dissociate();
            }
        }
        if (isset($parsedBody['roomAction'])) {
            $roomId = $parsedBody['roomAction']['roomId'] ?? '';
            /** @var ThermostatRoom $roomEntity */
            $roomEntity = $this->ensureExists($thermostat->rooms()->getQuery()->find(['id' => $roomId])->first());
            $room = new ThermostatRoomConfig([], $thermostat->roomsState[$roomId] ?? []);
            if (isset($parsedBody['roomAction']['clear'])) {
                $thermostat->log('Manualnie powrócono do automatycznego sterowania pomieszczeniem ' . $roomEntity->name);
                $room->clearForcedAction();
            } else {
                $time = $parsedBody['roomAction']['time'] ?? 30;
                Assertion::greaterThan($time, 0);
                $room->forceAction($parsedBody['roomAction']['action'], $time);
                $actionLabel = $room->isCooling() ? 'chłodzenia' : ($room->isHeating() ? 'ogrzewania' : 'brak');
                if ($thermostat->target != 'temperature') {
                    $actionLabel = $actionLabel == 'ogrzewania' ? 'nawilżania' : 'osuszania';
                }
                $timeLog = $time > 100 ? '' : " na $time minut";
                $thermostat->log("Manualnie ustalono akcję $actionLabel$timeLog dla pomieszczenia $roomEntity->name");
            }
            $room->updateState($thermostat, $parsedBody['roomAction']['roomId']);
        }
        if (isset($parsedBody['nextProfileChange'])) {
            $thermostat->nextProfileChange = new \DateTime($parsedBody['nextProfileChange']);
        }
        $thermostat->save();
        if ($thermostat->enabled) {
            $this->adjustThermostat($thermostat);
        }
        return $this->thermostatResponse($thermostat);
    }

    private function adjustThermostat(Thermostat $thermostat) {
        $command = new DispatchThermostatCommand();
        $command->adjust($thermostat);
    }

    private function thermostatResponse(Thermostat $thermostat) {
        $api = SuplaApi::getInstance($thermostat->user()->first());
        $channelsToFetch = [];
        $channels = [];
        foreach ($thermostat->rooms()->get() as $room) {
            $channelsToFetch = array_merge($channelsToFetch, $room->thermometers);
            $channelsToFetch = array_merge($channelsToFetch, $room->heaters ?? []);
            $channelsToFetch = array_merge($channelsToFetch, $room->coolers ?? []);
        }
        foreach (array_unique($channelsToFetch) as $channelId) {
            try {
                $channels[$channelId] = $api->getChannelWithState($channelId);
            } catch (SuplaApiException $e) {
                $channels[$channelId] = [];
            }
        }
        return $this->response(['id' => $thermostat->id,
            'label' => $thermostat->label,
            'enabled' => boolval($thermostat->enabled),
            'profiles' => $thermostat->profiles()->get(),
            'rooms' => $thermostat->rooms()->get(),
            'activeProfile' => $thermostat->activeProfile()->first(),
            'nextProfileChange' => $thermostat->nextProfileChange->format(\DateTime::ATOM),
            'channels' => $channels,
            'roomsState' => $thermostat->roomsState,
            'target' => $thermostat->target,
            'turnedOnDevices' => array_map(function ($channelId) use ($api) {
                return $api->getChannelWithState($channelId);
            }, $thermostat->devicesState ?? []),
            'slug' => $thermostat->slug,
        ]);
    }

    public function deleteAction($params) {
        $thermostat = $this->ensureExists(Thermostat::find($params)->first());
        if ($thermostat->userId != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $thermostat->log('Usunięto termostat');
        $thermostat->delete();
        return $this->response()->withStatus(204);
    }
}
