<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatRoom;
use suplascripts\models\User;

class ThermostatTables extends Migration
{
    public function change()
    {
        $this->createThermostatsTable();
        $this->createThermostatRoomsTable();
        $this->createThermostatProfilesTable();
    }

    private function createThermostatsTable()
    {
        $this->table(Thermostat::TABLE_NAME)
            ->addColumn(Thermostat::ENABLED, 'boolean', ['default' => false])
            ->addColumn(Thermostat::SLUG, 'string')
            ->addColumn(Thermostat::ROOMS_STATE, 'text')
            ->addColumn(Thermostat::DEVICES_STATE, 'text')
            ->addColumn(Thermostat::NEXT_PROFILE_CHANGE, 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addTimestamps(Thermostat::CREATED_AT, Thermostat::UPDATED_AT)
            ->addColumn(Thermostat::USER_ID, 'uuid')
            ->addForeignKey(Thermostat::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addColumn(Thermostat::ACTIVE_PROFILE_ID, 'uuid', ['null' => true])
            ->addForeignKey(Thermostat::ACTIVE_PROFILE_ID, ThermostatProfile::TABLE_NAME, ThermostatProfile::ID, ['delete' => 'SET_NULL'])
            ->create();
        $this->table(Thermostat::TABLE_NAME)
            ->changeColumn(Thermostat::ID, 'uuid')
            ->update();
    }

    private function createThermostatRoomsTable()
    {
        $this->table(ThermostatRoom::TABLE_NAME)
            ->addColumn(ThermostatRoom::NAME, 'string', ['length' => 100])
            ->addColumn(ThermostatRoom::THERMOMETERS, 'text')
            ->addColumn(ThermostatRoom::HEATERS, 'text')
            ->addColumn(ThermostatRoom::COOLERS, 'text')
            ->addTimestamps(ThermostatRoom::CREATED_AT, ThermostatRoom::UPDATED_AT)
            ->addColumn(ThermostatRoom::USER_ID, 'uuid')
            ->addForeignKey(ThermostatRoom::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addColumn(ThermostatRoom::THERMOSTAT_ID, 'uuid')
            ->addForeignKey(ThermostatRoom::THERMOSTAT_ID, Thermostat::TABLE_NAME, Thermostat::ID, ['delete' => 'CASCADE'])
            ->create();
        $this->table(ThermostatRoom::TABLE_NAME)
            ->changeColumn(ThermostatRoom::ID, 'uuid')
            ->update();
    }

    private function createThermostatProfilesTable()
    {
        $this->table(ThermostatProfile::TABLE_NAME)
            ->addColumn(ThermostatProfile::NAME, 'string', ['length' => 100])
            ->addColumn(ThermostatProfile::USER_ID, 'uuid')
            ->addColumn(ThermostatProfile::ROOMS_CONFIG, 'text')
            ->addColumn(ThermostatProfile::ACTIVE_ON, 'text')
            ->addTimestamps(ThermostatProfile::CREATED_AT, ThermostatRoom::UPDATED_AT)
            ->addForeignKey(ThermostatProfile::USER_ID, User::TABLE_NAME, User::ID, ['delete' => 'CASCADE'])
            ->addColumn(ThermostatProfile::THERMOSTAT_ID, 'uuid')
            ->addForeignKey(ThermostatProfile::THERMOSTAT_ID, Thermostat::TABLE_NAME, Thermostat::ID, ['delete' => 'CASCADE'])
            ->create();
        $this->table(ThermostatProfile::TABLE_NAME)
            ->changeColumn(ThermostatProfile::ID, 'uuid')
            ->update();
    }
}
