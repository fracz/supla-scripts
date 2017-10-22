<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;
use suplascripts\models\User;

class Scenes extends Migration
{
    public function change()
    {
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::SLUG, 'uuid', ['null' => true])
            ->addColumn(Scene::LABEL, 'string')
            ->addColumn(Scene::ACTIONS, 'text')
            ->addColumn(Scene::FEEDBACK, 'text')
            ->addColumn(Scene::LAST_USED, 'timestamp', ['null' => true])
            ->addColumn(Scene::USER_ID, 'uuid')
            ->addForeignKey(Scene::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Scene::CREATED_AT, Scene::UPDATED_AT)
            ->addIndex(Scene::SLUG, ['unique' => true])
            ->create();
        $this->table(Scene::TABLE_NAME)
            ->changeColumn(Scene::ID, 'uuid')
            ->update();
    }
}
