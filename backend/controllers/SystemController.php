<?php

namespace suplascripts\controllers;

use suplascripts\app\Application;
use suplascripts\app\authorization\JwtAndBasicAuthorizationMiddleware;

class SystemController extends BaseController {
    public function getInfoAction() {
        $auth = new JwtAndBasicAuthorizationMiddleware();
        $authError = $auth->authenticateWithJwt($this->request());
        $authenticated = !$authError;
        $response = [
            'version' => Application::version(),
            'authenticated' => $authenticated,
            'oAuthClientId' => $this->getApp()->getSetting('oauth')['clientId'] ?? null,
            'scriptsUrl' => $this->getApp()->getSetting('oauth')['scriptsUrl'] ?? 'https://supla.fracz.com',
            'cloudUrl' => $this->getApp()->getSetting('oauth')['cloudUrl'] ?? 'https://cloud.supla.org',
            'time' => (new \DateTime())->format(\DateTime::ATOM),
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
