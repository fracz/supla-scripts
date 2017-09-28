<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;

class ChannelsController extends BaseController
{
    use HasSuplaApi;

    public function getAction($params)
    {
        return $this->response($this->getApi()->getChannelWithState($params['id']));
    }

    public function executeAction($params)
    {
        $channelId = $params['id'];
        $body = $this->request()->getParsedBody();
        $action = $body['action'];
        Assertion::inArray($action, ['turnOn', 'turnOff', 'toggle', 'getChannelState']);
        $result = call_user_func_array([$this->getApi(), $action], [$channelId]);
        if ($result && $action != 'getChannelState') {
            $result = $this->getApi()->getChannelState($channelId);
        }
        return $this->response($result);
    }
}
