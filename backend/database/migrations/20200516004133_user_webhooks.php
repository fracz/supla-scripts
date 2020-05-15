<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class UserWebhooks extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::WEBHOOK_TOKEN, 'text')
            ->update();
    }
}
