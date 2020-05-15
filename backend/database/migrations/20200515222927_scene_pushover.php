<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;

class ScenePushover extends Migration {
    public function change() {
        $this->table(User::TABLE_NAME)
            ->addColumn(User::PUSHOVER_CREDENTIALS, 'text')
            ->update();
//        $this->table(Scene::TABLE_NAME)
//            ->addColumn(Scene::INTERVALS, 'text', ['null' => true])
//            ->addColumn(Scene::NEXT_EXECUTION_TIME, 'timestamp', ['null' => true])
//            ->update();
    }
}
