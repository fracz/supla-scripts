<?php

namespace suplascripts\app\commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCyclicTasksCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dispatch:all')
            ->setDescription('Dispatches all tasks that should be run periodically.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('dispatch:thermostat'), $output);
    }
}
