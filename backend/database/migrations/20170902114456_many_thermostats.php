<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\Thermostat;

class ManyThermostats extends Migration
{
    public function change()
    {
        $this->table(Thermostat::TABLE_NAME)
            ->addColumn(Thermostat::LABEL, 'string', ['default' => 'Default'])
            ->update();
    }
}
