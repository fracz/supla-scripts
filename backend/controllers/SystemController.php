<?php

namespace suplascripts\controllers;

use suplascripts\app\Application;
use suplascripts\app\authorization\JwtAndBasicAuthorizationMiddleware;
use suplascripts\models\User;

class SystemController extends BaseController {
    public function getInfoAction() {
        $auth = new JwtAndBasicAuthorizationMiddleware();
        $authError = $auth->authenticateWithJwt($this->request());
        $authenticated = !$authError;
        $response = [
            'version' => Application::version(),
            'authenticated' => $authenticated,
            'oAuthClientId' => $this->getApp()->getSetting('oauth')['clientId'] ?? null,
            'scriptsUrl' => $this->getApp()->getSetting('oauth')['scriptsUrl'] ?? 'https://scripts.supla.io',
            'cloudUrl' => $this->getApp()->getSetting('oauth')['cloudUrl'] ?? 'https://cloud.supla.org',
            'time' => (new \DateTime())->format(\DateTime::ATOM),
            'databaseStatus' => User::count() > 0 ? 'database ok' : 'database down',
        ];
        if ($authenticated) {
            $response['user'] = ['username' => $this->getCurrentUser()->username];
            if ($this->getApp()->getContainer()->has('currentClient')) {
                $response['client'] = ['label' => $this->getApp()->currentClient->label];
            }
        }
        return $this->response($response);
    }
}
