<?php

namespace suplascripts\app\commands;

use suplascripts\app\UserAndUrlAwareLogger;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatProfileTimeSpan;
use suplascripts\models\thermostat\ThermostatRoom;
use suplascripts\models\thermostat\ThermostatRoomConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchThermostatCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dispatch:thermostat')
            ->setDescription('Dispatches thermostat.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $activeThermostats = Thermostat::where([Thermostat::ENABLED => true])->get();
        foreach ($activeThermostats as $thermostat) {
            try {
                $this->adjust($thermostat, $output);
            } catch (\Exception $e) {
                (new UserAndUrlAwareLogger())->error($e->getMessage(), ['thermostat' => $thermostat->id]);
            }
        }
    }

    public function adjust(Thermostat $thermostat)
    {
        $this->changeProfileIfNeeded($thermostat);
        $this->chooseActionsForRooms($thermostat);
        $this->adjustDevicesToRoomActions($thermostat);
    }

    private function changeProfileIfNeeded(Thermostat $thermostat)
    {
        if ($thermostat->shouldChangeProfile()) {
            $activeProfile = $thermostat->activeProfile()->first();
            $nextProfileChange = new \DateTime(date('Y-m-d', strtotime('+1month')));
            foreach ($thermostat->profiles()->get() as $profile) {
                /** @var ThermostatProfile $profile */
                if ($profile->activeOn && count($profile->activeOn)) {
                    foreach ($profile->activeOn as $timeSpanArray) {
                        $timeSpan = new ThermostatProfileTimeSpan($timeSpanArray);
                        $closestStart = $timeSpan->getClosestStart();
                        $closestEnd = $timeSpan->getClosestEnd();
                        if ($closestStart > $closestEnd) { // active!
                            $thermostat->activeProfile()->associate($profile);
                            $thermostat->nextProfileChange = $closestEnd;
                            $thermostat->save();
                            $thermostat->log('Włączono profil ' . $profile->name);
                            return;
                        } else if ($closestStart < $nextProfileChange) {
                            $nextProfileChange = $closestStart;
                        }
                    }
                }
            }
            if ($activeProfile) {
                $thermostat->log('Wyłączono profil ' . $thermostat->activeProfile()->first()->name);
                $thermostat->activeProfile()->dissociate();
            }
            $thermostat->nextProfileChange = $nextProfileChange;
            $thermostat->save();
        }
    }

    private function chooseActionsForRooms(Thermostat $thermostat)
    {
        /** @var ThermostatProfile $profile */
        $profile = $thermostat->activeProfile()->first();
        $roomsConfig = $profile ? $profile->roomsConfig ?? [] : [];
        foreach ($thermostat->rooms()->get() as $room) {
            $roomState = $thermostat->roomsState[$room->id] ?? [];
            $roomConfig = $roomsConfig[$room->id] ?? [];
            $decidor = new ThermostatRoomConfig($roomConfig, $roomState);
            /** @var ThermostatRoom $room */
            if ($decidor->hasConfig()) {
                if ($decidor->hasForcedAction()) {
                    continue;
                }
                $currentTemperature = $room->getCurrentTemperature();
                $currentTemperatureFormatted = number_format($currentTemperature, 1) . '°C';
                if ($decidor->shouldCool($currentTemperature) && !$decidor->isCooling()) {
                    $thermostat->log("Rozpoczęto ochładzanie pomieszczenia $room->name, temperatura: $currentTemperatureFormatted");
                    $decidor->cool();
                } else if ($decidor->shouldHeat($currentTemperature) && !$decidor->isHeating()) {
                    $thermostat->log("Rozpoczęto ogrzewanie pomieszczenia $room->name, temperatura: $currentTemperatureFormatted");
                    $decidor->heat();
                } else if (!$decidor->shouldCool($currentTemperature) && !$decidor->shouldHeat($currentTemperature)
                    && ($decidor->isHeating() || $decidor->isCooling())) {
                    $thermostat->log("Zakończono ochładzanie lub ogrzewanie pomieszczenia $room->name, temperatura: $currentTemperatureFormatted");
                    $decidor->turnOff();
                }
                $decidor->updateState($thermostat, $room->id);
            } else if ($decidor->hasAction() && !$decidor->hasForcedAction()) {
                $decidor->turnOff();
                $decidor->updateState($thermostat, $room->id);
            }

        }
        $thermostat->save();
    }

    private function adjustDevicesToRoomActions(Thermostat $thermostat)
    {
        $desiredDevicesTurnedOn = [];
        foreach ($thermostat->rooms()->get() as $room) {
            /** @var ThermostatRoom $room */
            $decidor = new ThermostatRoomConfig([], $thermostat->roomsState[$room->id] ?? []);
            if ($decidor->isCooling()) {
                $desiredDevicesTurnedOn = array_merge($desiredDevicesTurnedOn, $room->coolers);
            } else if ($decidor->isHeating()) {
                $desiredDevicesTurnedOn = array_merge($desiredDevicesTurnedOn, $room->heaters);
            }
        }
        $actualDevicesTurnedOn = $thermostat->devicesState;
        $desiredDevicesTurnedOn = array_unique($desiredDevicesTurnedOn);
        $api = SuplaApi::getInstance($thermostat->user()->first());
        foreach (array_diff($desiredDevicesTurnedOn, $actualDevicesTurnedOn) as $channelIdToTurnOn) {
            $thermostat->log('Włączono kanał #' . $channelIdToTurnOn);
            if (!$api->turnOn($channelIdToTurnOn)) {
                $thermostat->log("Failed to turn on channel #" . $channelIdToTurnOn);
                $desiredDevicesTurnedOn = array_filter($desiredDevicesTurnedOn, function ($element) use ($channelIdToTurnOn) {
                    return $channelIdToTurnOn != $element;
                });
            }
        }
        foreach (array_diff($actualDevicesTurnedOn, $desiredDevicesTurnedOn) as $channelIdToTurnOff) {
            $thermostat->log('Wyłączono kanał #' . $channelIdToTurnOff);
            if (!$api->turnOff($channelIdToTurnOff)) {
                $thermostat->log("Failed to turn off channel #" . $channelIdToTurnOff);
                $desiredDevicesTurnedOn[] = $channelIdToTurnOff;
            }
        }
        $thermostat->devicesState = array_values(array_unique($desiredDevicesTurnedOn));
        $thermostat->save();
    }
}
