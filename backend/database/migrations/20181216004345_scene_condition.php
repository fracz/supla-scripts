<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class SceneCondition extends Migration {
    public function change() {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::CONDITION, 'text', ['null' => true])
            ->update();
    }
}
