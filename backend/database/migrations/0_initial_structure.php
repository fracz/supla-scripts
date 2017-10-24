<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class InitialStructure extends Migration {

    public function change() {
        $this->createUsersTable();
    }

    private function createUsersTable() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::USERNAME, 'string', ['length' => 100])
            ->addColumn(User::PASSWORD, 'string')
            ->addColumn(User::API_CREDENTIALS, 'text')
            ->addColumn(User::LAST_LOGIN_DATE, 'timestamp', ['null' => true])
            ->addIndex([User::USERNAME], ['unique' => true])
            ->addTimestamps(User::CREATED_AT, User::UPDATED_AT)
            ->create();
        $this->table(User::TABLE_NAME)
            ->changeColumn(User::ID, 'uuid')
            ->update();
    }
}
