<?php

namespace suplascripts\app\commands;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateDbCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:migrate')
            ->setDescription('Performs the database schema migration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = new PhinxApplication();
        $configPath = addcslashes(__DIR__, '\\') . '/../../database/phinx-config.php';
        $app->run(new StringInput("migrate -c $configPath"), $output);
    }
}
