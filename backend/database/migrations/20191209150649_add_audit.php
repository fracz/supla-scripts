<?php

use suplascripts\app\Application;
use suplascripts\database\migrations\Migration;
use suplascripts\models\AuditEntry;
use suplascripts\models\User;

class AddAudit extends Migration {
    public function change() {
        $this->table(AuditEntry::TABLE_NAME)
            ->addColumn(AuditEntry::USER_ID, 'uuid')
            ->addColumn(AuditEntry::CHANNEL_ID, 'integer', ['signed' => false])
            ->addColumn(AuditEntry::NEW_STATE, 'text')
            ->addForeignKey(AuditEntry::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->create();
        Application::getInstance()->db->getConnection()->update(sprintf(
            "ALTER TABLE %s ADD %s DATETIME(3) DEFAULT CURRENT_TIMESTAMP(3) NOT NULL;",
            AuditEntry::TABLE_NAME,
            AuditEntry::CREATED_AT
        ));
        $this->table(AuditEntry::TABLE_NAME)
            ->changeColumn(AuditEntry::ID, 'uuid')
            ->addIndex(AuditEntry::CREATED_AT)
            ->update();
    }
}
