<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\voice\VoiceCommand;

class VoiceCommandsNulls extends Migration {

    public function change() {
        $this->table(VoiceCommand::TABLE_NAME)
            ->changeColumn(VoiceCommand::SCENE, 'text', ['null' => true])
            ->changeColumn(VoiceCommand::FEEDBACK, 'text', ['null' => true])
            ->update();
    }
}
