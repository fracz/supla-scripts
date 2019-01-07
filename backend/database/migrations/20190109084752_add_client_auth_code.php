<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;

class AddClientAuthCode extends Migration {
    public function change() {
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::AUTH_CODE, 'int', ['null' => true, 'signed' => false, 'unique' => true])
            ->update();
    }
}
