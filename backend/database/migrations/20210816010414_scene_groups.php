<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneGroup;
use suplascripts\models\User;

class SceneGroups extends Migration {
    public function change() {
        $this->table(SceneGroup::TABLE_NAME)
            ->addColumn(SceneGroup::LABEL, 'string')
            ->addColumn(SceneGroup::COLLAPSED, 'boolean', ['default' => false])
            ->addColumn(SceneGroup::ORDINAL_NUMBER, 'integer', ['default' => 0])
            ->addColumn(SceneGroup::USER_ID, 'uuid')
            ->addForeignKey(SceneGroup::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(SceneGroup::CREATED_AT, SceneGroup::UPDATED_AT)
            ->create();
        $this->table(SceneGroup::TABLE_NAME)
            ->changeColumn(SceneGroup::ID, 'uuid')
            ->update();
        $this->table(Scene::TABLE_NAME)
            ->addColumn(Scene::GROUP_ID, 'uuid', ['null' => true])
            ->addForeignKey(Scene::GROUP_ID, SceneGroup::TABLE_NAME, SceneGroup::ID, ['delete' => 'SET_NULL'])
            ->addColumn(Scene::ORDINAL_NUMBER, 'integer', ['default' => 0])
            ->update();
    }
}
