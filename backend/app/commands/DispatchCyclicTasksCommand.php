<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCyclicTasksCommand extends Command {
    protected function configure() {
        $this
            ->setName('dispatch:all')
            ->setDescription('Dispatches all tasks that should be run periodically.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $minute = intval(date('H')) * 60 + intval(date('i'));
        $intervals = Application::getInstance()->getSetting('intervals', []);
        if ($minute % ($intervals['thermostat'] ?? 2) == 0) {
            $this->getApplication()->run(new StringInput('clear:client-registration-codes'), $output);
            $this->getApplication()->run(new StringInput('oauth:refresh-tokens'), $output);
        }
        if ($minute % ($intervals['thermostat'] ?? 5) == 0) {
            $this->getApplication()->run(new StringInput('dispatch:thermostat'), $output);
        }
        if ($minute % ($intervals['clearLogs'] ?? 60) == 0) {
            $this->getApplication()->run(new StringInput('clear:db-logs'), $output);
            $this->getApplication()->run(new StringInput('clear:request-quota-logs'), $output);
            $this->getApplication()->run(new StringInput('clear:invalid-user-tokens'), $output);
        }
        $this->getApplication()->run(new StringInput('metrics:gauges'), $output);
    }
}
