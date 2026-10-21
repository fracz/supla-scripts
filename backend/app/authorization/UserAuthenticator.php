<?php

namespace suplascripts\app\authorization;

use Tuupola\Middleware\HttpBasicAuthentication\AuthenticatorInterface;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\models\User;

class UserAuthenticator implements AuthenticatorInterface {

    public function __invoke(array $arguments): bool {
        if (!is_string($arguments['user'] ?? null) || !is_string($arguments['password'] ?? null)) {
            return false;
        }

        try {
            $this->findMatchingUser($arguments['user'], $arguments['password']);
            return true;
        } catch (ApiException $e) {
            return false;
        }
    }

    public function findMatchingUser($username, $plainPassword) {
        $user = User::findByUsername($username);
        if ($user != null && $user->isPasswordValid($plainPassword)) {
            return $user;
        }
        throw new ApiException('Invalid username or password', 401);
    }
}
