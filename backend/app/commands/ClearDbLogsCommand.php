<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use suplascripts\models\log\StateLogEntry;
use suplascripts\models\LogEntry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearDbLogsCommand extends Command {

    protected function configure() {
        $this
            ->setName('clear:db-logs')
            ->setDescription('Remove old logs from database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $time = Application::getInstance()->getSetting('clearLogsOlderThan', '1week');
        LogEntry::where(LogEntry::CREATED_AT, '<', new \DateTime('-' . $time))->delete();
        StateLogEntry::where(StateLogEntry::CREATED_AT, '<', new \DateTime('-' . $time))->delete();
    }
}
