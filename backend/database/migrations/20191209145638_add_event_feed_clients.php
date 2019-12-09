<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\EventFeedClient;
use suplascripts\models\User;

class AddEventFeedClients extends Migration {
    public function change() {
        $this->table(EventFeedClient::TABLE_NAME)
            ->addColumn(EventFeedClient::USER_ID, 'uuid')
            ->addColumn(EventFeedClient::LAST_CONNECTION_DATE, 'datetime', ['null' => true])
            ->addColumn(EventFeedClient::LAST_STATE, 'string', ['length' => 255])
            ->addColumn(EventFeedClient::ACCESS_ID_ID, 'integer', ['signed' => false])
            ->addColumn(EventFeedClient::ACCESS_ID_PASSWORD, 'text')
            ->addForeignKey(EventFeedClient::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(EventFeedClient::CREATED_AT, EventFeedClient::UPDATED_AT)
            ->create();
        $this->table(EventFeedClient::TABLE_NAME)
            ->changeColumn(EventFeedClient::ID, 'uuid')
            ->update();
    }
}
