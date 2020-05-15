<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class SceneCrontab extends Migration {
    public function change() {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::INTERVALS, 'text', ['null' => true])
            ->addColumn(Scene::NEXT_EXECUTION_TIME, 'timestamp', ['null' => true])
            ->update();
    }
}
