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
        $aboutToExpire = new \DateTime('+5 minutes');
        $aboutToExpire->setTimeZone(new \DateTimeZone('UTC'));
        /** @var User[] $users */
        $users = User::where(User::TOKEN_EXPIRATION_TIME, '<', $aboutToExpire)->get();
        $client = new OAuthClient();
        $output->writeln('Refreshing tokens of users: ' . count($users));
        foreach ($users as $user) {
            try {
                $client->refreshAccessToken($user);
            } catch (\Exception $e) {
                Application::getInstance()->logger->toOauthLog()->error('Could not refresh access token.', [
                    'message' => $e->getMessage(),
                    'userId' => $user->id,
                    'credentials' => $user->getApiCredentials(),
                ]);
            }
        }
    }
}
