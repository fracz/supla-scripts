<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class UserShortUniqueId extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::SHORT_UNIQUE_ID, 'string', ['null' => true])
            ->removeIndex([User::USERNAME])
            ->addIndex([User::USERNAME, User::SHORT_UNIQUE_ID], ['unique' => true])
            ->update();
    }
}
