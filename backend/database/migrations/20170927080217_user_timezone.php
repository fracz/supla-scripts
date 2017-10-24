<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class UserTimezone extends Migration {

    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::TIMEZONE, 'string', ['default' => 'Europe/Warsaw'])
            ->update();
    }
}
