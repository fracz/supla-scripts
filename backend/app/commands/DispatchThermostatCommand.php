<?php

namespace suplascripts\app\commands;

use Cron\CronExpression;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
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
        }
    }

    private function changeProfileIfNeeded(Thermostat $thermostat, OutputInterface $output)
    {
        $now = time();
        $activeProfile = $thermostat->activeProfile()->first();
        if ($thermostat->nextProfileChange->getTimestamp() <= $now) {
            $today = date('Y-m-d', $now);
            foreach ($thermostat->profiles()->get() as $profile) {
                /** @var ThermostatProfile $profile */
                if ($profile->activeOn && count($profile->activeOn)) {
                    foreach ($profile->activeOn as $timeSpan) {
                        $weekdays = $timeSpan['weekdays'] ?? [];
                        $weekdays = count($weekdays) ? implode(',', $weekdays) : '*';
                        $startTime = ($timeSpan['timeRange'] ?? [])['timeStart'] ?? 0;
                        $endTime = ($timeSpan['timeRange'] ?? [])['timeEnd'] ?? 1439;
                        $startTimeMinute = $startTime % 60;
                        $startTimeHour = floor($startTime / 60);
                        $endTimeMinute = $endTime % 60;
                        $endTimeHour = floor($endTime / 60);
                        $startCronExpression = "$startTimeMinute $startTimeHour * * $weekdays";
                        $endCronExpression = "$endTimeMinute $endTimeHour * * $weekdays";
                        $startsToday = CronExpression::factory($startCronExpression)->getNextRunDate($today, 0, true);
                        $endsToday = CronExpression::factory($endCronExpression)->getNextRunDate($today, 0, true);
                        if ($startsToday->getTimestamp() <= $now && $endsToday->getTimestamp() >= $now) {
                            if (!$activeProfile || $activeProfile->id != $profile->id) {
                                $thermostat->activeProfile()->associate($profile);
                                $thermostat->save();
                                $output->writeln('Updated active profile for thermostat ' . $thermostat->id);
                            }
                            return;
                        }
                    }
                }
            }
        }
        if ($activeProfile) {
            $thermostat->activeProfile()->dissociate();
            $thermostat->save();
            $output->writeln('No profile active for thermostat ' . $thermostat->id);
        }
    }
}
