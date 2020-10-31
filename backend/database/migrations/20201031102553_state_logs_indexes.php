<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\log\StateLogEntry;

class StateLogsIndexes extends Migration {
    public function change() {
        $this->table(StateLogEntry::TABLE_NAME)
            ->addIndex([StateLogEntry::USER_ID, StateLogEntry::CREATED_AT])
            ->addIndex([StateLogEntry::USER_ID, StateLogEntry::CHANNEL_ID])
            ->addIndex([StateLogEntry::CREATED_AT])
            ->update();
    }
}
