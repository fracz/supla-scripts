<?php

use suplascripts\app\Application;
use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class TimeScenes extends Migration {
    public function change() {
        $table = Scene::TABLE_NAME;
        $actions = Scene::ACTIONS;
        Application::getInstance()->db->getConnection()->update("UPDATE $table SET $actions = CONCAT('{0:\"', $actions, '\"}');");
    }
}
