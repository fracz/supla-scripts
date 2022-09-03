<?php

namespace suplascripts\app\commands;

use Assert\Assertion;
use suplascripts\app\Application;
use suplascripts\models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserUpdateCommand extends Command {
    protected function configure() {
        $this
            ->setName('user:update')
            ->addArgument('userId', InputArgument::REQUIRED)
            ->addOption('all', 'a', InputOption::VALUE_NONE)
            ->setDescription('Performs an update of an user account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance();
        /** @var User $user */
        $user = User::find($input->getArgument('userId'));
        Assertion::notNull($user);
        $io = new SymfonyStyle($input, $output);
        $io->writeln('Short unique ID: ' . $user->shortUniqueId ?: 'NONE');
        $io->writeln('Last login: ' . ($user->lastLoginDate ? $user->lastLoginDate->format(\DateTime::ATOM) : 'NEVER'));
        $io->writeln('Token expires on: ' . ($user->tokenExpirationTime ?: 'NONE'));
        if ($input->getOption('all')) {
            $io->section('API Settings');
            $io->writeln(json_encode($user->getApiCredentials(), JSON_PRETTY_PRINT));
        }
        $user->sceneLimit = max(10, intval($io->ask('Senes limit', $user->sceneLimit)));
        $user->save();
        $io->success('OK');
    }
}
