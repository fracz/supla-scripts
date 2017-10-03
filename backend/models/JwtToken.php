<?php

namespace suplascripts\models;

use Assert\Assertion;
use Firebase\JWT\JWT;
use suplascripts\app\Application;

class JwtToken
{
    private $tokenData = [];

    public static function create(): JwtToken
    {
        return new self();
    }

    public function rememberMe(bool $rememberMe = true): JwtToken
    {
        $this->tokenData['rememberMe'] = $rememberMe;
        return $this;
    }

    public function basedOnPreviousToken($currentToken): JwtToken
    {
        return $this
            ->user($currentToken->user)
            ->rememberMe($currentToken->rememberMe);
    }

    /** @param User|null $user */
    public function user($user): JwtToken
    {
        if ($user) {
            $this->tokenData['user'] = [
                'id' => $user->id,
                'username' => $user->username,
            ];
            if (method_exists($user, 'hasExpiredPassword')) {
                if ($user->hasExpiredPassword()) {
                    $this->expiredPassword();
                } else if (isset($this->tokenData['expiredPassword'])) {
                    unset($this->tokenData['expiredPassword']);
                }
            }
        }
        return $this;
    }

    public function client(Client $client): JwtToken
    {
        $this->tokenData['client'] = ['id' => $client->id];
        return $this;
    }

    public function issue(): string
    {
        $app = Application::getInstance();
        $jwtSettings = $app->getSetting('jwt');
        $now = time();
        $expirationTime = $jwtSettings[($this->tokenData['rememberMe'] ?? false) ? 'expirationTimeRememberMe' : 'expirationTime'];
        if (isset($this->tokenData['client']) && $this->tokenData['client']) {
            Assertion::keyNotExists($this->tokenData, 'user');
            $expirationTime = $jwtSettings['expirationTimeClient'];
        }
        $token = array_merge($this->tokenData, [
            'iss' => $jwtSettings['iss'], // issuer
            'iat' => $now, // issued at
            'nbf' => $now, // not before
            'exp' => $now + $expirationTime, // expires
        ]);
        $jwt = JWT::encode($token, $jwtSettings['key']);
        return $jwt;
    }
}
