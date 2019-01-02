<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\Thermostat;

class ThermostatFailureCount extends Migration {
    public function change() {
        $this->table(Thermostat::TABLE_NAME)
            ->addColumn(Thermostat::FAILURE_COUNT, 'integer', ['signed' => false, 'default' => 0])
            ->update();
    }
}
