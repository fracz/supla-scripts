<?php

use suplascripts\app\Application;
use suplascripts\database\migrations\Migration;
use suplascripts\models\notification\Notification;
use suplascripts\models\scene\Scene;
use suplascripts\models\User;

class Notifications extends Migration {
    public function change() {
        $table = Scene::TABLE_NAME;
        $actions = Scene::ACTIONS;
        Application::getInstance()->db->getConnection()->update("UPDATE $table SET $actions = CONCAT('{\"0\":\"', $actions, '\"}');");

        $this->table(Notification::TABLE_NAME)
            ->addColumn(Notification::LABEL, 'string')
            ->addColumn(Notification::CONFIG, 'text')
            ->addColumn(Notification::USER_ID, 'uuid')
            ->addForeignKey(Notification::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Notification::CREATED_AT, Notification::UPDATED_AT)
            ->create();

        $this->table(Notification::TABLE_NAME)
            ->changeColumn(Notification::ID, 'uuid')
            ->update();
    }
}
