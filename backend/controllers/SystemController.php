<?php

namespace suplascripts\controllers;

use suplascripts\models\client\CoordinationToken;

class SystemController extends BaseController
{
    public function getTimeAction()
    {
        return $this->response((new \DateTime())->format(\DateTime::ATOM));
    }
}
