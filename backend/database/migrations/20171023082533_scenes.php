<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\scene\Scene;

class Scenes extends Migration
{
    public function change()
    {
        $this->table("voice_commands")
            ->rename(Scene::TABLE_NAME)
            ->addColumn(Scene::SLUG, 'uuid', ['null' => true])
            ->addColumn(Scene::LABEL, 'string', ['default' => 'scene label'])
            ->renameColumn('triggers', Scene::VOICE_TRIGGERS)
            ->addIndex(Scene::SLUG, ['unique' => true])
            ->update();
        Scene::all()->each([$this, 'createSceneLabelFromTheFirstVoiceTrigger']);
    }

    public function createSceneLabelFromTheFirstVoiceTrigger(Scene $scene)
    {
        $scene->label = $scene->voiceTriggers[0];
        $scene->save();
    }
}
