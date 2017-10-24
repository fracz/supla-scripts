<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\LogEntry;
use suplascripts\models\User;

class Logs extends Migration {

    public function change() {
        $this->table(LogEntry::TABLE_NAME)
            ->addColumn(LogEntry::MODULE, 'string')
            ->addColumn(LogEntry::DATA, 'text')
            ->addColumn(LogEntry::USER_ID, 'uuid')
            ->addForeignKey(LogEntry::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(LogEntry::CREATED_AT, LogEntry::UPDATED_AT)
            ->create();
        $this->table(LogEntry::TABLE_NAME)
            ->changeColumn(LogEntry::ID, 'uuid')
            ->update();
    }
}
