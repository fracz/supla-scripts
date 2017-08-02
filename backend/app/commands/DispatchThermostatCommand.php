<?php

namespace suplascripts\app\commands;

use Cron\CronExpression;
use suplascripts\controllers\thermostat\ThermostatRoomConfig;
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
                $thermostat->nextProfileChange = $closestStart;
                $thermostat->save();
                $output->writeln('No profile active for thermostat ' . $thermostat->id);
            }
        }
    }

    private function chooseActionsForRooms(Thermostat $thermostat, OutputInterface $output)
    {
        /** @var ThermostatProfile $profile */
        $profile = $thermostat->activeProfile()->first();
        $roomsConfig = $profile ? $profile->roomsConfig ?? [] : [];
        if (!count($roomsConfig)) {
            $thermostat->roomsState = [];
        } else {
            foreach ($thermostat->rooms()->get() as $room) {
                /** @var ThermostatRoom $room */
                if (isset($roomsConfig[$room->id])) {
                    $roomConfig = $roomsConfig[$room->id];
                    $roomState = $thermostat->roomsState[$room->id] ?? [];
                    $decidor = new ThermostatRoomConfig($roomConfig, $roomState);
                    $currentTemperature = $room->getCurrentTemperature();
                    if ($decidor->shouldCool($currentTemperature) && !$decidor->isCooling()) {
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
                    $thermostat->roomsState = array_merge($thermostat->roomsState, [$room->id => $decidor->getState()]);
                }
            }
        }
        $thermostat->save();
    }
}
