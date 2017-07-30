<?php

namespace suplascripts\controllers;

use Assert\Assert;
use Slim\Http\Response;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\models\JwtToken;
use suplascripts\models\User;

class TokensController extends BaseController
{
    public function createTokenAction()
    {
        $body = $this->request()->getParsedBody();
        return $this->authenticateUser($body);
    }

    private function authenticateUser(array $body): Response
    {
        Assert::that($body)->notEmptyKey(User::USERNAME)->notEmptyKey(User::PASSWORD);
        $usernameOrEmail = $body[User::USERNAME];
        $password = $body[User::PASSWORD];
        $user = $this->findMatchingUser($usernameOrEmail, $password);
        $token = JwtToken::create()->user($user)->rememberMe($body['rememberMe'] ?? false)->issue();
        $user->trackLastLogin();
        return $this->response(['token' => $token]);
    }

    public function refreshTokenAction()
    {
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

    private function findMatchingUser($username, $plainPassword)
    {
        $user = User::findByUsername($username);
        if ($user != null && $user->isPasswordValid($plainPassword)) {
            return $user;
        }
        throw new ApiException('Invalid username or password', 401);
    }
}
