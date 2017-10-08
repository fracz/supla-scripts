<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\User;
use suplascripts\models\voice\VoiceCommand;

class VoiceCommands extends Migration
{
    public function change()
    {
        $this->table(VoiceCommand::TABLE_NAME)
            ->addColumn(VoiceCommand::TRIGGERS, 'text')
            ->addColumn(VoiceCommand::SCENE, 'text')
            ->addColumn(VoiceCommand::FEEDBACK, 'text')
            ->addColumn(VoiceCommand::LAST_USED, 'timestamp', ['null' => true])
            ->addColumn(VoiceCommand::USER_ID, 'uuid')
            ->addForeignKey(VoiceCommand::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(VoiceCommand::CREATED_AT, VoiceCommand::UPDATED_AT)
            ->create();
        $this->table(VoiceCommand::TABLE_NAME)
            ->changeColumn(VoiceCommand::ID, 'uuid')
            ->update();

        $this->table(User::TABLE_NAME)
            ->addColumn(User::LAST_VOICE_COMMAND, 'text', ['null' => true])
            ->update();
    }
}
