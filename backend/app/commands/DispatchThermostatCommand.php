<?php

namespace suplascripts\app\commands;

use Cron\CronExpression;
use suplascripts\controllers\thermostat\ThermostatRoomConfig;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatProfileTimeSpan;
use suplascripts\models\thermostat\ThermostatRoom;
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
        $output->writeln('<info>Active thermostats: ' . count($activeThermostats) . '</info>');
        foreach ($activeThermostats as $thermostat) {
            $this->changeProfileIfNeeded($thermostat, $output);
            $this->chooseActionsForRooms($thermostat, $output);
            $this->adjustDevicesToRoomActions($thermostat, $output);
        }
    }

    private function changeProfileIfNeeded(Thermostat $thermostat, OutputInterface $output)
    {
        $now = time();
        if ($thermostat->nextProfileChange->getTimestamp() <= $now) {
            $activeProfile = $thermostat->activeProfile()->first();
            $closestStart = new \DateTime(date('Y-m-d', strtotime('+1day')));
            $today = date('Y-m-d', $now);
            foreach ($thermostat->profiles()->get() as $profile) {
                /** @var ThermostatProfile $profile */
                if ($profile->activeOn && count($profile->activeOn)) {
                    foreach ($profile->activeOn as $timeSpanArray) {
                        $timeSpan = new ThermostatProfileTimeSpan($timeSpanArray);
                        $startsToday = CronExpression::factory($timeSpan->getStartCronExpression())->getNextRunDate($today, 0, true);
                        $endsToday = CronExpression::factory($timeSpan->getEndCronExpression())->getNextRunDate($today, 0, true);
                        if ($startsToday->getTimestamp() <= $now && $endsToday->getTimestamp() >= $now) {
                            if (!$activeProfile || $activeProfile->id != $profile->id) {
                                $thermostat->activeProfile()->associate($profile);
                                $thermostat->nextProfileChange = $endsToday;
                                $thermostat->save();
                                $output->writeln('Updated active profile for thermostat ' . $thermostat->id);
                            }
                            return;
                        } else if ($startsToday->getTimestamp() > $now && $startsToday < $closestStart) {
                            $closestStart = $startsToday;
                        }
                    }
                }
            }
            if ($activeProfile) {
                $thermostat->activeProfile()->dissociate();
                $output->writeln('Deactivated all profiles for thermostat ' . $thermostat->id);
            }
            $thermostat->nextProfileChange = $closestStart;
            $thermostat->save();
        }
    }

    private function chooseActionsForRooms(Thermostat $thermostat, OutputInterface $output)
    {
        /** @var ThermostatProfile $profile */
        $profile = $thermostat->activeProfile()->first();
        $roomsConfig = $profile ? $profile->roomsConfig ?? [] : [];
        foreach ($thermostat->rooms()->get() as $room) {
            /** @var ThermostatRoom $room */
            if (isset($roomsConfig[$room->id])) {
                $roomConfig = $roomsConfig[$room->id];
                $roomState = $thermostat->roomsState[$room->id] ?? [];
                $decidor = new ThermostatRoomConfig($roomConfig, $roomState);
                $currentTemperature = $room->getCurrentTemperature();
                if ($decidor->hasForcedAction()) {
                } else if ($decidor->shouldCool($currentTemperature) && !$decidor->isCooling()) {
                    $output->writeln('Started cooling of room ' . $room->id);
                    $decidor->cool();
                } else if ($decidor->shouldHeat($currentTemperature) && !$decidor->isHeating()) {
                    $output->writeln('Started heating of room ' . $room->id);
                    $decidor->heat();
                } else if (!$decidor->shouldCool($currentTemperature) && !$decidor->shouldHeat($currentTemperature)
                    && ($decidor->isHeating() || $decidor->isCooling())) {
                    $output->writeln('Turned off cooling and heating of room ' . $room->id);
                    $decidor->turnOff();
                }
                $decidor->updateState($thermostat, $room->id);
            }

        }
        $thermostat->save();
    }

    private function adjustDevicesToRoomActions(Thermostat $thermostat, OutputInterface $output)
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
        $api = new SuplaApi($thermostat->user()->first());
        foreach (array_diff($desiredDevicesTurnedOn, $actualDevicesTurnedOn) as $channelIdToTurnOn) {
            $output->writeln("Turning on channel " . $channelIdToTurnOn);
            if (!$api->turnOn($channelIdToTurnOn)) {
                $output->writeln("Failed to turn on channel " . $channelIdToTurnOn);
                $desiredDevicesTurnedOn = array_filter($desiredDevicesTurnedOn, function ($element) use ($channelIdToTurnOn) {
                    return $channelIdToTurnOn != $element;
                });
            }
        }
        foreach (array_diff($actualDevicesTurnedOn, $desiredDevicesTurnedOn) as $channelIdToTurnOff) {
            $output->writeln("Turning off channel " . $channelIdToTurnOff);
            if (!$api->turnOff($channelIdToTurnOff)) {
                $output->writeln("Failed to turn off channel " . $channelIdToTurnOn);
                $desiredDevicesTurnedOn[] = $channelIdToTurnOff;
            }
        }
        $thermostat->devicesState = array_values(array_unique($desiredDevicesTurnedOn));
        $thermostat->save();
    }
}
