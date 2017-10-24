<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MetricsReleaseCommand extends Command {

    protected function configure() {
        $this
            ->setName('metrics:release')
            ->setDescription('Sends an info to the metrics that new version has been released.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance()->metrics->increment('release');
        Application::getInstance()->metrics->send();
    }
}
