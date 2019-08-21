<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearInvalidUserTokensCommand extends Command {
    protected function configure() {
        $this
            ->setName('clear:invalid-user-tokens')
            ->setDescription('Clear user tokens that could not be refreshed for a significant amount of time.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance()->db->getConnection()
            ->statement('UPDATE users SET tokenExpirationTime = null WHERE tokenExpirationTime < DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY)');
    }
}
