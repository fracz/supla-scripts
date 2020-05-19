<?php

namespace suplascripts\controllers;

use suplascripts\models\log\StateLogEntry;

class StateLogsController extends BaseController {
    public function getLatestAction() {
        $limit = min(100, intval($this->request()->getParam('limit', 100)));
        $channelId = $this->request()->getParam('channelId', null);
        $page = $this->request()->getParam('page', 0);
        $filters = [StateLogEntry::USER_ID => $this->getCurrentUser()->id];
        if ($channelId) {
            $filters[StateLogEntry::CHANNEL_ID] = $channelId;
        }
        $logs = StateLogEntry::where($filters)
            ->orderBy(StateLogEntry::CREATED_AT, 'desc')
            ->limit($limit)
            ->offset($page * $limit)
            ->get();
        return $this->response($logs);
    }
}
