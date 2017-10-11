<?php

namespace suplascripts\controllers;

use suplascripts\models\LogEntry;

class LogsController extends BaseController
{
    public function getLatestAction()
    {
        $limit = min(100, intval($this->request()->getParam('limit', 100)));
        $entityId = $this->request()->getParam('entityId', null);
        $filters = [LogEntry::USER_ID => $this->getCurrentUser()->id];
        if ($entityId) {
            $filters[LogEntry::ENTITY_ID] = $entityId;
        }
        $logs = LogEntry::where($filters)
            ->orderBy(LogEntry::CREATED_AT, 'desc')
            ->limit($limit)
            ->get();
        return $this->response($logs);
    }
}
