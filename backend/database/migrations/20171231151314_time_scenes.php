<?php

use suplascripts\app\Application;
use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\PendingScene;
use suplascripts\models\scene\Scene;

class TimeScenes extends Migration {
    public function change() {
        $table = Scene::TABLE_NAME;
        $actions = Scene::ACTIONS;
        Application::getInstance()->db->getConnection()->update("UPDATE $table SET $actions = CONCAT('{\"0\":\"', $actions, '\"}');");

        $this->table(PendingScene::TABLE_NAME)
            ->addColumn(PendingScene::EXECUTE_AFTER, 'timestamp', ['default' => '2018-01-01 00:00:00'])
            ->addColumn(PendingScene::ACTIONS, 'text')
            ->addColumn(PendingScene::SCENE_ID, 'uuid')
            ->addForeignKey(PendingScene::SCENE_ID, Scene::TABLE_NAME, Scene::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(PendingScene::CREATED_AT, PendingScene::UPDATED_AT)
            ->addIndex(PendingScene::EXECUTE_AFTER)
            ->create();

        $this->table(PendingScene::TABLE_NAME)
            ->changeColumn(PendingScene::ID, 'uuid')
            ->update();
    }
}
