<?php

namespace suplascripts\controllers;

class SystemController extends BaseController
{
    public function getTimeAction()
    {
        return $this->response((new \DateTime())->format(\DateTime::ATOM));
    }
}
