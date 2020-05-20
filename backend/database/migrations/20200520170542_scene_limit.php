<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class SceneLimit extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::SCENE_LIMIT, 'integer', ['null' => false, 'default' => 100])
            ->update();
    }
}
