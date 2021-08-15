<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class SceneEnabled extends Migration {
    public function change() {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::ENABLED, 'boolean', ['default' => true])
            ->update();
    }
}
