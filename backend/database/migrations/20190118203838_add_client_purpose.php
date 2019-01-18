<?php

use Phinx\Db\Adapter\MysqlAdapter;
use suplascripts\app\Application;
use suplascripts\database\migrations\Migration;
use suplascripts\models\Client;

class AddClientPurpose extends Migration {
    public function change() {
        $table = Client::TABLE_NAME;
        $purpose = Client::PURPOSE;
        $this->table(Client::TABLE_NAME)
            ->addColumn(Client::PURPOSE, 'integer', ['default' => 0, 'limit' => MysqlAdapter::INT_TINY, 'signed' => false])
            ->update();
        $purposeAutomate = Client::PURPOSE_AUTOMATE;
        $purposeScene = Client::PURPOSE_SCENE;
        Application::getInstance()->db->getConnection()->update("UPDATE $table SET $purpose = $purposeScene WHERE sceneId IS NOT NULL");
        Application::getInstance()->db->getConnection()->update("UPDATE $table SET $purpose = $purposeAutomate WHERE $purpose = 0");
    }
}
