<?php

namespace suplascripts\app\commands;

use Assert\Assertion;
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
        Assertion::true(set_time_limit(90), 'Could not set the script execution time limit.');
        $intervals = Application::getInstance()->getSetting('intervals', []);
        $phpPath = Application::getInstance()->getSetting('phpPath', '/usr/local/bin/php');
        $interval = $intervals['timeScenes'] ?? 5;
        $sleep = 0;
        $command = $phpPath . ' ' . __DIR__ . '/../../../supla-scripts dispatch:time-scenes-execution';
        while ($sleep < 60) {
            $process = new Process("sleep $sleep && $command");
            $process->start();
            $sleep += $interval;
        }
        $process->wait();
        $output->writeln($process->getOutput());
        $output->writeln($process->getErrorOutput());
    }
}
