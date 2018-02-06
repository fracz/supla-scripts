<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\notification\Notification;
use suplascripts\models\notification\NotificationClientAssignment;

class NotificationClients extends Migration {
    public function change() {
        $this->table(Notification::TABLE_NAME)
            ->addColumn(Notification::CLIENT_IDS, 'text', ['null' => true])
            ->addColumn(Notification::SPEECH, 'text', ['null' => true])
            ->addColumn(Notification::DISPLAY_IF_DISCONNECTED, 'boolean', ['default' => false])
            ->update();
    }
}
