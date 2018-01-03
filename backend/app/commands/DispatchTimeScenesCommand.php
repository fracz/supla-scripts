<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DispatchTimeScenesCommand extends Command {

    protected function configure() {
        $this
            ->setName('dispatch:time-scenes')
            ->setDescription('Dispatches time scenes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $intervals = Application::getInstance()->getSetting('intervals', []);
        $interval = $intervals['timeScenes'] ?? 5;
        $sleep = 0;
        $command = 'php ' . __DIR__ . '/../../../supla-scripts dispatch:time-scenes-execution';
        while ($sleep < 60) {
            $process = new Process("sleep $sleep && $command");
            $process->start();
            $sleep += $interval;
        }
        $process->wait();
    }
}
