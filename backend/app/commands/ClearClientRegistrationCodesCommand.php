<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearClientRegistrationCodesCommand extends Command {

    protected function configure() {
        $this
            ->setName('clear:client-registration-codes')
            ->setDescription('Delete unused client registration codes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance()->db->getConnection()
            ->statement('DELETE FROM clients WHERE registrationCode IS NOT NULL AND createdAt < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 5 MINUTE)');
    }
}
