<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class UserTokenExpirationTime extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::TOKEN_EXPIRATION_TIME, 'timestamp', ['null' => true])
            ->update();
    }
}
