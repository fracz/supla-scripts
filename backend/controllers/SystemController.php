<?php

namespace suplascripts\controllers;

use suplascripts\app\Application;
use suplascripts\app\authorization\JwtAndBasicAuthorizationMiddleware;

class SystemController extends BaseController
{
    public function getTimeAction()
    {
        return $this->response((new \DateTime())->format(\DateTime::ATOM));
    }

    public function getInfoAction()
    {
        $auth = new JwtAndBasicAuthorizationMiddleware();
        $authError = $auth->authenticateWithJwt($this->request());
        $authenticated = !$authError;
        $response = [
            'version' => Application::version(),
            'authenticated' => $authenticated,
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
