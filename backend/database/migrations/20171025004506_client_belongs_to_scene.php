<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;
use suplascripts\models\scene\Scene;

class ClientBelongsToScene extends Migration {
    public function change() {
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::SCENE_ID, 'uuid', ['null' => true])
            ->addForeignKey(Client::SCENE_ID, Scene::TABLE_NAME, Scene::ID, ['delete' => 'CASCADE'])
            ->update();
    }
}
