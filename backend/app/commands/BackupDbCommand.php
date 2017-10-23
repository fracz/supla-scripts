<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class BackupDbCommand extends Command
{
    const BACKUP_DIR = Application::VAR_PATH . '/backups';

    protected function configure()
    {
        $this
            ->setName('db:backup')
            ->setDescription('Saves database backup to the backups directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbSettings = Application::getInstance()->getSetting('db');
        if ($dbSettings['driver'] != 'mysql') {
            $output->writeln('<warning>Only MySQL database backups are supported.</warning>');
            $this->askIfContinue($input, $output);
        } else {
            $this->backupMysqlDatabase($dbSettings, $input, $output);
        }

    }

    private function backupMysqlDatabase(array $dbSettings, InputInterface $input, OutputInterface $output)
    {
        $backupName = 'supla-scripts-' . date('YmdHis') . '-v' . Application::version() . '.sql.gz';
        $backupPath = self::BACKUP_DIR . '/' . $backupName;
        $process = new Process(sprintf(
            'mysqldump --user="%s" --password="%s" --host="%s" --lock-all-tables "%s" | gzip > "%s"',
            $dbSettings['username'],
            $dbSettings['password'],
            $dbSettings['host'],
            $dbSettings['database'],
            $backupPath
        ));
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln('<info>Database backup has been saved to ' . $backupName . '.</info>');
        } else {
            $output->writeln($process->getErrorOutput());
            $output->writeln('<error>Could not make the database backup.</error>');
            $this->askIfContinue($input, $output);
        }
    }


    private function askIfContinue(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $want = $helper->ask($input, $output, new ConfirmationQuestion('Do you want to continue without backup? [y/N] ', false));
        if (!$want) {
            throw new \RuntimeException('Could not create database backup.');
        }
    }
}
