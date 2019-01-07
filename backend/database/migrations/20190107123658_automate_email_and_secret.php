<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class AutomateEmailAndSecret extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::AUTOMATE_EMAIL, 'string', ['null' => true])
            ->addColumn(User::AUTOMATE_SECRET, 'text', ['null' => true])
            ->update();
    }
}
