<?php

namespace suplascripts\controllers;

use Assert\Assert;
use Assert\Assertion;
use Slim\Http\Response;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\models\Client;
use suplascripts\models\JwtToken;
use suplascripts\models\supla\OAuthClient;
use suplascripts\models\supla\SuplaApiClientWithOAuthSupport;
use suplascripts\models\User;

class TokensController extends BaseController {

    public function createTokenAction() {
        $body = $this->request()->getParsedBody();
        return $this->authenticateUser($body);
    }

    private function authenticateUser(array $body): Response {
        Assert::that($body)->notEmptyKey(User::USERNAME)->notEmptyKey(User::PASSWORD);
        $usernameOrEmail = $body[User::USERNAME];
        $password = $body[User::PASSWORD];
        $user = $this->findMatchingUser($usernameOrEmail, $password);
        $token = JwtToken::create()->user($user)->rememberMe($body['rememberMe'] ?? false)->issue();
        $this->getApp()->getContainer()['currentUser'] = $user;
        $user->trackLastLogin();
        return $this->response(['token' => $token]);
    }

    public function createTokenForClientAction() {
        $body = $this->request()->getParsedBody();
        $this->authenticateUser($body);
        return $this->getApp()->db->getConnection()->transaction(function () use ($body) {
            $client = new Client([Client::LABEL => $body['label'] ?? 'Client']);
            $client->save();
            $token = JwtToken::create()->client($client)->issue();
            return $this->response(['token' => $token])->withStatus(201);
        });
    }

    public function oauthAuthenticateAction() {
        $body = $this->request()->getParsedBody();
        Assert::that($body)->notEmptyKey('authCode');
        $oauthClient = new OAuthClient();
        $code = $body['authCode'];

        $suplaAddress = $oauthClient->getSuplaAddress($code);
        $apiCredentials = $oauthClient->issueNewAccessTokens($suplaAddress, [
            'grant_type' => 'authorization_code',
            'redirect_uri' => 'http://suplascripts.local/authorize',
            'code' => $code,
        ]);

        $suplaApi = new SuplaApiClientWithOAuthSupport($apiCredentials, false, false, false);
        $userData = $suplaApi->remoteRequest(null, '/api/users/current', 'GET', true);
        Assertion::isObject($userData);
        $email = $userData->email;

        $user = User::findByUsername($email);
        if (!$user && ($compatUsername = $userData->oauthCompatUsername ?? null)) {
            $user = $this->findByCompatUsername($compatUsername, $suplaAddress);
            if ($user) {
                $user->username = $email;
            }
        }
        if (!$user) {
            $user = User::create([
                User::USERNAME => $userData['email'],
                User::API_CREDENTIALS => $apiCredentials,
                User::TIMEZONE => $userData->timezone ?? 'Europe/Warsaw',
            ]);
        }

        $user->setApiCredentials($apiCredentials);
        $user->save();

        $token = JwtToken::create()->user($user)->rememberMe($body['rememberMe'] ?? false)->issue();
        $this->getApp()->getContainer()['currentUser'] = $user;
        $user->trackLastLogin();
        return $this->response(['token' => $token]);
    }

    public function refreshTokenAction() {
        $currentToken = $this->getApp()->currentToken;
        $user = $this->getCurrentUser();
        $newToken = JwtToken::create()
            ->basedOnPreviousToken($currentToken)
            ->user($user)
            ->issue();
        if ($user) {
            $user->trackLastLogin();
        }
        return $this->response(['token' => $newToken]);
    }

    private function findMatchingUser($username, $plainPassword) {
        $user = User::findByUsername($username);
        if ($user != null && $user->isPasswordValid($plainPassword)) {
            return $user;
        }
        throw new ApiException('Invalid username or password', 401);
    }

    private function findByCompatUsername($compatUsername, $suplaAddress) {
        $suplaDomain = preg_replace('#^https?://#', '', $suplaAddress);
        $users = User::all();
        foreach ($users as $user) {
            $apiCredentials = $user->getApiCredentials();
            $username = $apiCredentials['username'] ?? null;
            if ($username == $compatUsername && ($apiCredentials['server'] ?? '') == $suplaDomain) {
                return $user;
            }
        }
    }
}
