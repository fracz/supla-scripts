<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\supla\SuplaApiException;

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
        $params = explode(',', $body['action']);
        $action = array_shift($params);
        Assertion::inArray($action, ['turnOn', 'turnOff', 'toggle', 'getChannelState', 'setRgb']);
        array_unshift($params, $channelId);
        $result = call_user_func_array([$this->getApi(), $action], $params);
        if ($result === false) {
            throw new SuplaApiException($this->getApi()->getClient(), 'Could not execute the action.');
        }
        if ($result && $action != 'getChannelState') {
            $result = $this->getApi()->getChannelState($channelId);
        }
        return $this->response($result);
    }
}
