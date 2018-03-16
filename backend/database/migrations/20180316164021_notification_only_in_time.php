<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\notification\Notification;
use suplascripts\models\notification\NotificationClientAssignment;

class NotificationOnlyInTime extends Migration {
    public function change() {
        $this->table(Notification::TABLE_NAME)
            ->addColumn(Notification::ONLY_IN_TIME, 'boolean', ['default' => false])
            ->update();

        $notifications = Notification::all();
        foreach ($notifications as $notification) {
            try {
                @$notification->calculateNextNotificationTime();
            } catch (RuntimeException $e) {
                $notification->update([Notification::INTERVALS => '*/15 * * * *']);
                $notification->save();
                echo "Fixed notification " . $notification->id . PHP_EOL;
            }
        }
    }
}
