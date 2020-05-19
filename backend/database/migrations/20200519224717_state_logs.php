<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\log\StateLogEntry;
use suplascripts\models\User;

class StateLogs extends Migration {
    public function change() {
        $this->table(StateLogEntry::TABLE_NAME)
            ->addColumn(StateLogEntry::USER_ID, 'uuid')
            ->addColumn(StateLogEntry::CHANNEL_ID, 'id')
            ->addColumn(StateLogEntry::STATE, 'text')
            ->addForeignKey(StateLogEntry::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addColumn(StateLogEntry::CREATED_AT, 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->create();

        $this->table(StateLogEntry::TABLE_NAME)
            ->changeColumn(StateLogEntry::ID, 'uuid')
            ->update();
    }
}
