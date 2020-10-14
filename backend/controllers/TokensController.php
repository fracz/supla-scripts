<?php

namespace suplascripts\controllers;

use Assert\Assert;
use Assert\Assertion;
use Ramsey\Uuid\Uuid;
use Slim\Http\Response;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\models\Client;
use suplascripts\models\JwtToken;
use suplascripts\models\supla\ChannelFunction;
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
        $client = null;
        if (isset($body[User::USERNAME])) {
            $this->authenticateUser($body);
        } else {
            Assertion::keyExists($body, 'registrationCode', 'Invalid auth request. Try updating SUPLA Scripts Configurator.');
            /** @var Client $client */
            $client = Client::where(Client::REGISTRATION_CODE, $body['registrationCode'])->first();
            if (!$client) {
                throw new ApiException('Invalid registration code. Try again.');
            }
        }
        return $this->getApp()->db->getConnection()->transaction(function () use ($body, $client) {
            if (!$client) {
                $client = new Client([]);
            }
            $client->label = $body['label'] ?? 'Client';
            $client->registrationCode = null;
            $client->active = true;
            $client->purpose = Client::PURPOSE_AUTOMATE;
            $client->save();
            $token = JwtToken::create()->client($client)->issue();
            return $this->response(['token' => $token])->withStatus(201);
        });
    }

    public function oauthAuthenticateAction() {
        $body = $this->request()->getParsedBody();
        if (isset($body['personalToken'])) {
            return $this->authenticateWithToken($body);
        }
        if (isset($body[User::USERNAME])) {
            return $this->authenticateUser($body);
        }
        Assert::that($body)->notEmptyKey('authCode');
        $oauthClient = new OAuthClient();
        $code = $body['authCode'];

        $suplaAddress = $oauthClient->getSuplaAddress($code);
        $apiCredentials = $oauthClient->issueNewAccessTokens(
            [
                'grant_type' => 'authorization_code',
                'redirect_uri' => ($this->getApp()->getSetting('oauth')['scriptsUrl'] ?? 'https://supla.fracz.com') . '/authorize',
                'code' => $code,
            ]
        );

        $suplaApi = new SuplaApiClientWithOAuthSupport(null, $apiCredentials, false, false, false);
        $userData = $suplaApi->remoteRequest(null, '/api/users/current', 'GET', true);
        Assertion::isObject($userData);
        $email = $userData->email;
        $user = $this->findUserBasedOnUserData($suplaAddress, $userData);
        if (!$user) {
            $user = User::create([
                User::SHORT_UNIQUE_ID => $userData->shortUniqueId,
//                User::USERNAME => $email,
                User::API_CREDENTIALS => $apiCredentials,
                User::TIMEZONE => $userData->timezone ?? 'Europe/Warsaw',
            ]);
        }

        $user->setApiCredentials($apiCredentials);
        $user->save();

        $token = JwtToken::create()->user($user, $email)->rememberMe($body['rememberMe'] ?? false)->issue();
        $this->getApp()->getContainer()['currentUser'] = $user;
        $user->trackLastLogin();
        $this->registerStateWebhook($user, $suplaApi);
        return $this->response(['token' => $token]);
    }

    protected function findUserBasedOnUserData(string $suplaAddress, $userData) {
        $user = User::where(User::SHORT_UNIQUE_ID, $userData->shortUniqueId)->first();
        if (!$user && ($compatUsername = $userData->oauthCompatUserName ?? null)) {
            $user = $this->findByCompatUsername($compatUsername, $suplaAddress);
            /** @var User $user */
            if ($user) {
                $user->shortUniqueId = $userData->shortUniqueId;
                $user->timezone = $userData->timezone ?? 'Europe/Warsaw';
            }
        }
        return $user;
    }

    private function findByCompatUsername($compatUsername, $suplaAddress) {
        $suplaDomain = preg_replace('#^https?://#', '', $suplaAddress);
//        $suplaDomain = 'svr3.supla.org';
        /** @var User[] $users */
        $users = User::where(User::SHORT_UNIQUE_ID, null)->get();
        foreach ($users as $user) {
            $apiCredentials = $user->getApiCredentials();
            $username = $apiCredentials['username'] ?? null;
            if ($username == $compatUsername && ($apiCredentials['server'] ?? '') == $suplaDomain) {
                return $user;
            }
        }
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

    public function checkPersonalAccessTokenAction() {
        $body = $this->request()->getParsedBody();
        Assertion::keyExists($body, 'token');
        $token = $body['token'];
        list($suplaUrl, $userData) = $this->getUserDataBasedOnPersonalToken($token);
        $user = $this->findUserBasedOnUserData($suplaUrl, $userData);
        return $this->response([
            'userId' => $user ? $user->id : null,
            'username' => $user ? $user->username : null,
            'cloudUrl' => $suplaUrl,
            'cloudUsername' => $userData->email,
        ]);
    }

    private function authenticateWithToken(array $body): Response {
        $token = $body['personalToken'];
        list($suplaUrl, $userData) = $this->getUserDataBasedOnPersonalToken($token);
        $user = $this->findUserBasedOnUserData($suplaUrl, $userData);
        $apiCredentials = ['personal_token' => $token, 'target_url' => $suplaUrl];
        if ($user) {
            if ($user->username) {
                $newPassword = $body['newPassword'] ?? null;
                if ($newPassword) {
                    $user->setPassword($newPassword);
                }
            } else {
                Assertion::keyExists($body, 'username');
                Assertion::keyExists($body, 'password');
                User::validateUsernameUnique($body['username']);
                $user->username = $body['username'];
                $user->setPassword($body['password']);
            }
        } else {
            Assertion::keyExists($body, 'username');
            Assertion::keyExists($body, 'password');
            $user = User::create(
                [User::USERNAME => $body['username'], User::PASSWORD => $body['password'], User::API_CREDENTIALS => $apiCredentials]
            );
            $user->shortUniqueId = $userData->shortUniqueId;
        }
        $user->setApiCredentials($apiCredentials);
        $user->save();
        $token = JwtToken::create()->user($user)->issue();
        $this->getApp()->getContainer()['currentUser'] = $user;
        $user->trackLastLogin();
        return $this->response(['token' => $token]);
    }

    private function findMatchingUser($username, $plainPassword) {
        $user = User::findByUsername($username);
        if ($user != null && $user->isPasswordValid($plainPassword)) {
            return $user;
        }
        throw new ApiException('Invalid username or password', 401);
    }

    protected function getUserDataBasedOnPersonalToken($token): array {
        $oauthClient = new OAuthClient();
        $suplaUrl = $oauthClient->getSuplaAddress($token);
        Assertion::url($suplaUrl, 'Invalid token (no encoded SUPLA Cloud URL).');
        $suplaApi = new SuplaApiClientWithOAuthSupport(null, ['personal_token' => $token, 'target_url' => $suplaUrl], false, false, false);
        $tokenInfo = $suplaApi->remoteRequest(null, '/api/token-info', 'GET', true);
        Assertion::isObject($tokenInfo, "Invalid token (SUPLA Cloud $suplaUrl does not authorize it).");
        $scopes = explode(' ', $tokenInfo->scope);
        $missingScopes = array_diff(OAuthClient::REQUIRED_SCOPES, $scopes);
        Assertion::count($missingScopes, 0, 'Your token is missing some scopes: ' . implode(', ', $missingScopes));
        $userData = $suplaApi->remoteRequest(null, '/api/users/current', 'GET', true);
        return array($suplaUrl, $userData);
    }

    private function registerStateWebhook(User $user, SuplaApiClientWithOAuthSupport $api) {
        $user->webhookToken = sha1(Uuid::getFactory()->uuid4());
        $webhookRequest = [
            'url' => ($this->getApp()->getSetting('oauth')['scriptsUrl'] ?? 'https://supla.fracz.com') . '/api/state-webhook',
            'accessToken' => sha1($user->webhookToken),
            'refreshToken' => $user->webhookToken,
            'expiresAt' => strtotime('+1 month'),
            'expiresIn' => strtotime('+1 month') - time(),
            'functions' => ChannelFunction::getFunctionsToRegisterInStateWebhook(),
        ];
        $hook = $api->remoteRequest($webhookRequest, '/api/integrations/state-webhook', 'PUT', true);
        if (!$hook) {
            $user->webhookToken = null;
        }
        $user->save();
    }
}
