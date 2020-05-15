<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\scene\NotificationSender;
use suplascripts\models\User;

class UsersController extends BaseController {

    public function postAction() {
        $parsedBody = $this->request()->getParsedBody();
        $parsedBody = array_intersect_key($parsedBody, [User::USERNAME => '', User::PASSWORD => '', User::API_CREDENTIALS => '']);
        return $this->getApp()->db->getConnection()->transaction(function () use ($parsedBody) {
            $createdUser = User::create($parsedBody);
            return $this->response($createdUser)
                ->withStatus(201);
        });
    }

    public function getAction($params) {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        $userData = $user->toArray();
        return $this->response($userData);
    }

    public function patchAction($params) {
        $this->ensureAuthenticated();
        $user = $this->getUser($params);
        if ($user->id != $this->getCurrentUser()->id) {
            throw new Http403Exception();
        }
        return $this->getApp()->db->getConnection()->transaction(function () use ($user) {
            $request = $this->request()->getParsedBody();
            if (isset($request['delete'])) {
                Assertion::true($this->getCurrentUser()->isPasswordValid($request['delete']), 'Current password is not valid');
                $user->delete();
                return $this->response()->withStatus(204);
            }
            if (isset($request['newPassword'])) {
                Assertion::notEmptyKey($request, 'currentPassword');
                Assertion::notEq($request['newPassword'], $request['currentPassword']);
                Assertion::true($user->isPasswordValid($request['currentPassword']), 'Current password is not valid');
                $user->log('user', 'Zmieniono hasło do konta');
                $user->setPassword($request['newPassword']);
            }
            if (isset($request['apiCredentials'])) {
                $user->log('user', 'Zmieniono dane do SUPLA API');
                $user->setApiCredentials($request['apiCredentials']);
            }
            if (isset($request['pushoverCredentials'])) {
                $user->setPushoverCredentials($request['pushoverCredentials']);
                $user->log('user', 'Zmieniono dane do Pushover');
            }
            if (isset($request['testPushover'])) {
                $sent = (new NotificationSender($user))->send(['title' => 'SUPLA Scripts', 'message' => 'Test']);
                return $this->response(null)->withStatus($sent ? 200 : 400);
            }
            if (isset($request['timezone'])) {
                $user->log('user', 'Zmieniono strefę czasową');
                $user->setTimezone(new \DateTimeZone($request['timezone']));
            }
            $user->save();
            return $this->response($user);
        });
    }

    private function getUser($params): User {
        if ($params['id'] == 'current') {
            $user = $this->getCurrentUser();
        } else {
            $user = $this->ensureExists(User::find($params['id'] ?? -1));
        }
        return $user;
    }
}
