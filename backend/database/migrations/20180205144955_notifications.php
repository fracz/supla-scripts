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
            ->addColumn(Notification::CONDITION, 'text')
            ->addColumn(Notification::MIN_CONDITIONS, 'integer', ['default' => 1])
            ->addColumn(Notification::INTERVALS, 'text')
            ->addColumn(Notification::RETRY_INTERVAL, 'integer', ['default' => 60])
            ->addColumn(Notification::HEADER, 'text')
            ->addColumn(Notification::MESSAGE, 'text')
            ->addColumn(Notification::ICON, 'integer', ['default' => 0])
            ->addColumn(Notification::SOUND, 'boolean', ['default' => false])
            ->addColumn(Notification::VIBRATE, 'boolean', ['default' => false])
            ->addColumn(Notification::FLASH, 'boolean', ['default' => false])
            ->addColumn(Notification::CANCELLABLE, 'boolean', ['default' => true])
            ->addColumn(Notification::ONGOING, 'boolean', ['default' => false])
            ->addColumn(Notification::AWAKE, 'boolean', ['default' => false])
            ->addColumn(Notification::ACTIONS, 'text', ['null' => true])
            ->addColumn(Notification::USER_ID, 'uuid')
            ->addForeignKey(Notification::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addTimestamps(Notification::CREATED_AT, Notification::UPDATED_AT)
            ->create();

        $this->table(Notification::TABLE_NAME)
            ->changeColumn(Notification::ID, 'uuid')
            ->update();
    }
}
