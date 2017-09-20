<?php

namespace suplascripts\controllers;

use suplascripts\models\HasSuplaApi;

class DevicesController extends BaseController
{
    use HasSuplaApi;

    public function getListAction()
    {
        return $this->response($this->getApi()->getDevices());
    }
}
