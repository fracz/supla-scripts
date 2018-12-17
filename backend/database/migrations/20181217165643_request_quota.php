<?php

use suplascripts\app\authorization\IpRequestQuotaMiddleware;
use suplascripts\database\migrations\Migration;

class RequestQuota extends Migration {
    public function change() {
        $this->table(
            IpRequestQuotaMiddleware::TABLE_NAME,
            ['id' => false, 'primary_key' => [IpRequestQuotaMiddleware::IP, IpRequestQuotaMiddleware::TIMESTAMP]]
        )
            ->addColumn(IpRequestQuotaMiddleware::IP, 'integer', ['signed' => false])
            ->addColumn(IpRequestQuotaMiddleware::TIMESTAMP, 'integer', ['signed' => false])
            ->addColumn(IpRequestQuotaMiddleware::COUNT, 'integer', ['default' => 1, 'signed' => false])
            ->create();
    }
}
