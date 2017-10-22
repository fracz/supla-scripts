<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeApplicationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initializes this supla-scripts instance.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logo = file_exists(__DIR__ . '/../../../scripts/logo.txt')
            ? file_get_contents(__DIR__ . '/../../../scripts/logo.txt')
            : file_get_contents(__DIR__ . '/../../logo.txt');
        $output->writeln($logo);
        $output->writeln('SUPLA Scripts v' . Application::version());
        $output->writeln('');
        if (!file_exists(Application::CONFIG_PATH) || !is_readable(Application::CONFIG_PATH)) {
            $output->writeln('<error>There is no config.json.</error>');
        } else {
            $this->getApplication()->setAutoExit(false);
            $this->getApplication()->run(new StringInput('encryptionKey:generate'), $output);
            $this->getApplication()->run(new StringInput('clear:cache'), $output);
            $this->getApplication()->run(new StringInput('db:migrate'), $output);
        }
    }
}
