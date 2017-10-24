<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;
use suplascripts\models\User;

class Clients extends Migration {

    public function change() {
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::LABEL, 'string')
            ->addColumn(Client::ACTIVE, 'boolean', ['default' =>  true])
            ->addColumn(Client::LAST_CONNECTION_DATE, 'timestamp', ['default' => '2017-01-01 00:00:00'])
            ->addColumn(Client::USER_ID, 'uuid')
            ->addForeignKey(Client::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Client::CREATED_AT, Client::UPDATED_AT)
            ->create();
        $this->table(Client::TABLE_NAME)
            ->changeColumn(Client::ID, 'uuid')
            ->update();
    }
}
