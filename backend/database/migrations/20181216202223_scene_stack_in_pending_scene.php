<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\PendingScene;

class SceneStackInPendingScene extends Migration {
    public function change() {
        $this->table(PendingScene::TABLE_NAME)
            ->addColumn(PendingScene::SCENE_STACK, 'text', ['null' => true])
            ->update();
    }
}
