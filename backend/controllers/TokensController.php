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
        if (isset($body[User::USERNAME])) {
            return $this->authenticateUser($body);
        }
        Assert::that($body)->notEmptyKey('authCode');
        $oauthClient = new OAuthClient();
        $code = $body['authCode'];

        $suplaAddress = $oauthClient->getSuplaAddress($code);
        $apiCredentials = $oauthClient->issueNewAccessTokens($suplaAddress, [
            'grant_type' => 'authorization_code',
            'redirect_uri' => ($this->getApp()->getSetting('oauth')['scriptsUrl'] ?? 'https://supla.fracz.com') . '/authorize',
            'code' => $code,
        ]);

        $suplaApi = new SuplaApiClientWithOAuthSupport($apiCredentials, false, false, false);
        $userData = $suplaApi->remoteRequest(null, '/api/users/current', 'GET', true);
        Assertion::isObject($userData);
        $email = $userData->email;
        $shortUniqueId = $userData->shortUniqueId;

        $user = User::where(User::SHORT_UNIQUE_ID, $shortUniqueId)->first();
        if (!$user && ($compatUsername = $userData->oauthCompatUsername ?? null)) {
            $user = $this->findByCompatUsername($compatUsername, $suplaAddress);
            if ($user) {
                $user->username = $email;
            }
        }
        if (!$user) {
            $user = User::create([
                User::SHORT_UNIQUE_ID => $shortUniqueId,
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

    public function checkPersonalAccessTokenAction() {
        $body = $this->request()->getParsedBody();
        Assertion::keyExists($body, 'token');
        $token = $body['token'];
        $oauthClient = new OAuthClient();
        $suplaUrl = $oauthClient->getSuplaAddress($token);
        Assertion::url($suplaUrl, 'Invalid token (no encoded SUPLA Cloud URL).');
        $suplaApi = new SuplaApiClientWithOAuthSupport(['access_token' => $token, 'server' => $suplaUrl], false, false, false);
        $tokenInfo = $suplaApi->remoteRequest(null, '/api/token-info', 'GET', true);
        Assertion::isObject($tokenInfo, "Invalid token (SUPLA Cloud $suplaUrl does not authorize it).");
        $scopes = explode(' ', $tokenInfo->scope);
        $missingScopes = array_diff(OAuthClient::REQUIRED_SCOPES, $scopes);
        Assertion::count($missingScopes, 0, 'Your token is missing some scopes: ' . implode(', ', $missingScopes));
        $userData = $suplaApi->remoteRequest(null, '/api/users/current', 'GET', true);
        $user = User::where(User::SHORT_UNIQUE_ID, $tokenInfo->userShortUniqueId)->first();
        return $this->response([
            'userId' => $user ? $user->id : null,
            'username' => $user ? $user->username : null,
            'cloudUrl' => $suplaUrl,
            'cloudUsername' => $userData->email,
        ]);
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
