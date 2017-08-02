<?php

namespace suplascripts\app\commands;

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
            $this->changeProfileIfNeeded($thermostat);
        }
    }

    private function changeProfileIfNeeded(Thermostat $thermostat)
    {
        if ($thermostat->nextProfileChange->getTimestamp() <= time()) {
            foreach ($thermostat->profiles()->get() as $profile) {
                /** @var ThermostatProfile $profile */
                if ($profile->activeOn && count($profile->activeOn)) {
                    foreach ($profile->activeOn as $timeSpan) {

                    }
                }
            }
        }
    }
}
