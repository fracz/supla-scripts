<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearRequestQuotaLogsCommand extends Command {

    protected function configure() {
        $this
            ->setName('clear:request-quota-logs')
            ->setDescription('Remove old request quota logs from database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance()->db->getConnection()
            ->statement('DELETE FROM api_quota WHERE minute_timestamp < FLOOR(UNIX_TIMESTAMP() / 60) - 10');
    }
}
