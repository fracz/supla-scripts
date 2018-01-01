<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;
use suplascripts\models\User;

class VoiceCommands extends Migration {
    public function change() {
        $this->table('voice_commands')
            ->addColumn('triggers', 'text')
            ->addColumn('scene', 'text')
            ->addColumn(Scene::FEEDBACK, 'text')
            ->addColumn(Scene::LAST_USED, 'timestamp', ['null' => true])
            ->addColumn(Scene::USER_ID, 'uuid')
            ->addForeignKey(Scene::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Scene::CREATED_AT, Scene::UPDATED_AT)
            ->create();
        $this->table('voice_commands')
            ->changeColumn(Scene::ID, 'uuid')
            ->update();

        $this->table(User::TABLE_NAME)
            ->addColumn(User::LAST_VOICE_COMMAND, 'text', ['null' => true])
            ->update();
    }
}
