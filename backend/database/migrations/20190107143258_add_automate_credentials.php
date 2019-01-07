<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;
use suplascripts\models\User;

class AddAutomateCredentials extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::AUTOMATE_CREDENTIALS, 'text', ['null' => true])
            ->update();
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::AUTH_CODE, 'uuid', ['null' => true])
            ->update();
    }
}
