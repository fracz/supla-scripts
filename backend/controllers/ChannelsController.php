<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\scene\SceneExecutor;
use suplascripts\models\supla\SuplaApiException;

class ChannelsController extends BaseController {

    use HasSuplaApi;

    public function getAction($params) {
        return $this->response($this->getApi()->getChannelWithState($params['id']));
    }

    public function executeAction($params) {
        $sceneExecutor = new SceneExecutor();
        $channelId = $params['id'];
        $body = $this->request()->getParsedBody();
        Assertion::notEmptyKey($body, 'action');
        $operation = $channelId . SceneExecutor::CHANNEL_DELIMITER . $body['action'];
        $result = $sceneExecutor->executeCommandFromString($operation, $this->getCurrentUser());
        if ($result === false) {
            throw new SuplaApiException($this->getApi()->getClient(), 'Could not execute the action.');
        }
        if ($result && is_bool($result)) {
            usleep(50000);
            $result = $this->getApi()->getChannelState($channelId);
        }
        return $this->response($result);
    }

    public function getSensorLogsAction($params) {
        $logs = $this->getApi()->getSensorLogs(
            $params['id'],
            $this->request()->getParam('startDate', '-1hour'),
            $this->request()->getParam('endDate', 'now')
        );
        return $this->response($logs);
    }
}
