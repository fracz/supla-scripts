<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class SceneTrigger extends Migration {
    public function change() {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::TRIGGER, 'text', ['null' => true])
            ->addColumn(Scene::TRIGGER_CHANNELS, 'text', ['null' => true])
            ->addColumn(Scene::LAST_TRIGGER_STATE, 'boolean', ['default' => true])
            ->update();
    }
}
