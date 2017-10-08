<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\LogEntry;

class LogEntryEntities extends Migration
{
    public function change()
    {
        $this->table(LogEntry::TABLE_NAME)
            ->addColumn(LogEntry::ENTITY_ID, 'uuid', ['null' => true])
            ->update();
    }
}
