<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class SceneActionTriggers extends Migration {
    public function change() {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::ACTION_TRIGGERS, 'text', ['null' => true])
            ->update();
    }
}
