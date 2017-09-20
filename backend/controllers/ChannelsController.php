<?php

namespace suplascripts\controllers;

use suplascripts\models\HasSuplaApi;

class ChannelsController extends BaseController
{
    use HasSuplaApi;

    public function getAction($params)
    {
        return $this->response($this->getApi()->getChannelWithState($params['id']));
    }
}
