<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\ThermostatProfile;

class ClearProfileConditions extends Migration
{
    public function change()
    {
        $this->execute('UPDATE ' . ThermostatProfile::TABLE_NAME . ' SET ' . ThermostatProfile::ACTIVE_ON . '="[]"');
    }
}
