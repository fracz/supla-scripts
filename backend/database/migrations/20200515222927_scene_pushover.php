<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;
use suplascripts\models\User;

class ScenePushover extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::PUSHOVER_CREDENTIALS, 'text', ['null' => true])
            ->update();
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::NOTIFICATIONS, 'text', ['null' => true])
            ->update();
    }
}
