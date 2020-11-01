<?php

namespace suplascripts\controllers;

use Carbon\Carbon;
use suplascripts\models\log\StateLogEntry;

class StateLogsController extends BaseController {
    public function getLatestAction() {
//        for ($i = 0; $i < 50; $i++) {
//            $this->getCurrentUser()->stateLogs()->create([
//                StateLogEntry::CHANNEL_ID => 3,
//                StateLogEntry::STATE => ['on' => rand(0, 1) == 0, 'connected' => rand(0, 1) == 0],
//                StateLogEntry::CREATED_AT => Carbon::createFromTimestamp(rand(time() - 864000, time()))
//            ]);
//        }

        $limit = min(100, intval($this->request()->getParam('limit', 100)));
        $channelId = $this->request()->getParam('channelId', null);
        $filters = [StateLogEntry::USER_ID => $this->getCurrentUser()->id];
        if ($channelId) {
            $filters[StateLogEntry::CHANNEL_ID] = $channelId;
        }
        $logs = StateLogEntry::where($filters);
        $before = $this->request()->getParam('before', 0);
        if ($before) {
            $logs = $logs->where(StateLogEntry::CREATED_AT, '<', Carbon::createFromTimestamp($before, new \DateTimeZone('UTC')));
        }
        $logs = $logs
            ->orderBy(StateLogEntry::CREATED_AT, 'desc')
            ->limit($limit)
            ->get();
        return $this->response($logs);
    }
}
