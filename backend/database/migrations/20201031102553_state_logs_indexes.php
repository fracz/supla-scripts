<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\log\StateLogEntry;

class StateLogsIndexes extends Migration {
    public function change() {
        $this->table(StateLogEntry::TABLE_NAME)
            ->addIndex([StateLogEntry::USER_ID, StateLogEntry::CREATED_AT], ['name' => 'state_logs_user_id_created_at_idx'])
            ->addIndex([StateLogEntry::USER_ID, StateLogEntry::CHANNEL_ID], ['name' => 'state_logs_user_id_channel_id_idx'])
            ->addIndex([StateLogEntry::CREATED_AT], ['name' => 'state_logs_created_at_idx'])
            ->update();
    }
}
