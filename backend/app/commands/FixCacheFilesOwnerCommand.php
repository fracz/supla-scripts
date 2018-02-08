<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixCacheFilesOwnerCommand extends Command {
    protected function configure() {
        $this
            ->setName('cache:fix-owner')
            ->setDescription('Sets cache files owner to desired one.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $path = realpath(Application::VAR_PATH . '/cache');
        var_dump($path);
        if ($path) {
            system('chown -R www-data:www-data ' . $path);
        }
    }
}
