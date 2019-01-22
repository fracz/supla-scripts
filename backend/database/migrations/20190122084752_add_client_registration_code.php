<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;

class AddClientRegistrationCode extends Migration {
    public function change() {
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::REGISTRATION_CODE, 'integer', ['null' => true, 'signed' => false])
            ->addIndex(Client::REGISTRATION_CODE, ['unique' => true])
            ->update();
    }
}
