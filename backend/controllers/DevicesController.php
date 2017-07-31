<?php

namespace suplascripts\controllers;

use suplascripts\models\supla\SuplaApi;

class DevicesController extends BaseController
{
    public function getListAction()
    {
        $this->ensureAuthenticated();
        $api = new SuplaApi($this->getCurrentUser());
        return $this->response($api->getDevices());
    }
}
