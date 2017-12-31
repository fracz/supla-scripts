<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class VoiceCommandsNulls extends Migration {
    public function change() {
        $this->table('voice_commands')
            ->changeColumn('scene', 'text', ['null' => true])
            ->changeColumn(Scene::FEEDBACK, 'text', ['null' => true])
            ->update();
    }
}
