<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use suplascripts\models\supla\OAuthClient;
use suplascripts\models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OauthRefreshTokensCommand extends Command {

    protected function configure() {
        $this
            ->setName('oauth:refresh-tokens')
            ->setDescription('Refresh tokens that are about to expire.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Application::getInstance();
        $aboutToExpire = new \DateTime('+8 minutes', new \DateTimeZone('UTC'));
        /** @var User[] $users */
        $users = User::where(User::TOKEN_EXPIRATION_TIME, '<', $aboutToExpire)
            ->orderBy(USER::TOKEN_EXPIRATION_TIME, 'ASC')
            ->get();
        $client = new OAuthClient();
        $usersCount = count($users);
        $usersCounter = 0;
        if ($output->isVerbose()) {
            $output->writeln('Refreshing tokens of users: ' . $usersCount);
        }
        foreach ($users as $user) {
            if ($output->isVerbose() || $output->isVeryVerbose()) {
                $output->write('User ' . $user->id . '...');
            }
            try {
                $client->refreshAccessToken($user);
                if ($output->isVerbose() || $output->isVeryVerbose()) {
                    $output->write(' OK');
                }
            } catch (\Exception $e) {
                if ($output->isVerbose() || $output->isVeryVerbose()) {
                    $output->write(' FAILED!');
                }
                if ($output->isVeryVerbose()) {
                    $timezone = new \DateTimeZone('Europe/Warsaw');
                    $date = new \DateTime($user->tokenExpirationTime, new \DateTimeZone('UTC'));
                    $output->writeln("\nToken expiration time: " . $date->setTimezone($timezone)->format(\DateTime::ATOM));
                    $output->writeln($e->getMessage());
                    $output->writeln(json_encode($user->getApiCredentials()));
                }
                Application::getInstance()->logger->toOauthLog()->error('Could not refresh access token.', [
                    'message' => $e->getMessage(),
                    'userId' => $user->id,
                    'credentials' => $user->getApiCredentials(),
                ]);
            }
            if ($output->isVerbose() || $output->isVeryVerbose()) {
                $output->writeln(' ' . (++$usersCounter) . '/' . $usersCount);
            }
        }
    }
}
