<?php

namespace suplascripts\controllers;

use suplascripts\models\supla\SuplaApi;

class ChannelsController extends BaseController
{
    public function getAction($params)
    {
        $this->ensureAuthenticated();
        $api = new SuplaApi($this->getCurrentUser());
        return $this->response($api->getChannelWithState($params['id']));
    }
}
