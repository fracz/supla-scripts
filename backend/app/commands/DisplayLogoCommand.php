<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayLogoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('logo')
            ->setDescription('Displays SUPLA Scripts logo.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logo = file_exists(__DIR__ . '/../../../scripts/logo.txt')
            ? file_get_contents(__DIR__ . '/../../../scripts/logo.txt')
            : file_get_contents(__DIR__ . '/../../logo.txt');
        $output->writeln(rtrim($logo));
        $version = 'v' . Application::version();
        $pad = str_repeat(' ', 28 - strlen($version));
        $output->writeln('<info>' . $pad . $version . '</info>');
        $output->writeln('');
    }
}
