<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\User;

class UsersController extends BaseController
{
    public function postAction()
    {
        $parsedBody = $this->request()->getParsedBody();
        $parsedBody = array_intersect_key($parsedBody, [User::USERNAME => '', User::PASSWORD => '', User::API_CREDENTIALS => '']);
        return $this->getApp()->db->getConnection()->transaction(function () use ($parsedBody) {
            $createdUser = User::create($parsedBody);
            return $this->response($createdUser)
                ->withStatus(201);
        });
    }

    public function getAction($params)
    {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $userData = $user->toArray();
        return $this->response($userData);
    }

    public function patchAction($params)
    {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        return $this->getApp()->db->getConnection()->transaction(function () use ($user) {
            $request = $this->request()->getParsedBody();
            if (isset($request['newPassword'])) {
                Assertion::notEmptyKey($request, 'currentPassword');
                Assertion::notEq($request['newPassword'], $request['currentPassword']);
                Assertion::true($user->isPasswordValid($request['currentPassword']), 'Current password is not valid', 'currentPassword');
                $user->setPassword($request['newPassword']);
                $user->expirePasswordInTheNextCentury();
            }
            $user->save();
            return $this->response($user);
        });
    }

    public function putAction($params)
    {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        return $this->getApp()->db->getConnection()->transaction(function () use ($user) {
            $request = $this->request()->getParsedBody();
//            $user->name = $request['name'] ?? null;
            $user->save();
            return $this->getAction(['id' => $user->id]);
        });
    }

    public function deleteAction($params)
    {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $user->delete();
        return $this->response()->withStatus(204);
    }

    private function getUser($params): User
    {
        if ($params['id'] == 'current') {
            $user = $this->getCurrentUser();
        } else {
            $user = $this->ensureExists(User::find($params['id'] ?? -1));
        }
        return $user;
    }
}
