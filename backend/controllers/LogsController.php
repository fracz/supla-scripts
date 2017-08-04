<?php

namespace suplascripts\controllers;

use suplascripts\models\LogEntry;

class LogsController extends BaseController
{
    public function getLatestAction()
    {
        $logs = LogEntry::where([LogEntry::USER_ID => $this->getCurrentUser()->id])
            ->orderBy(LogEntry::CREATED_AT, 'desc')
            ->limit(100)
            ->get();
        return $this->response($logs);
    }
}
