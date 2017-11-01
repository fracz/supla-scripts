<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\Thermostat;

class ThermostatTarget extends Migration {
    public function change() {
        $this->table(Thermostat::TABLE_NAME)
            ->addColumn(Thermostat::TARGET, 'string', ['default' => 'temperature'])
            ->update();
    }
}
