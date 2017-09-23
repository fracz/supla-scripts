<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;
use suplascripts\models\User;

class Clients extends Migration
{
    public function change()
    {
        $this->$this->table(Client::TABLE_NAME)
            ->addColumn(Client::LABEL, 'string')
            ->addColumn(Client::ACTIVE, 'boolean')
            ->addColumn(Client::LAST_CONNECTION_DATE, 'timestamp')
            ->addColumn(Client::USER_ID, 'uuid')
            ->addForeignKey(Client::USER_ID, Client::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Client::CREATED_AT, Client::UPDATED_AT)
            ->create();
        $this->table(Client::TABLE_NAME)
            ->changeColumn(Client::ID, 'uuid')
            ->update();
    }
}
