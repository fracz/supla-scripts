<?php

namespace suplascripts\tests\database;

use suplascripts\models\log\StateLogEntry;
use suplascripts\models\scene\Scene;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;

class UserIsolationTest extends DatabaseTestCase {

    public function testUserRelationshipsAreScopedAndAssignOwnership() {
        $firstUser = $this->createUser();
        $secondUser = $this->createUser();

        $firstScene = $firstUser->scenes()->create([
            Scene::LABEL => 'First user scene',
            Scene::ACTIONS => ['10;turnOn'],
        ]);
        $secondScene = $secondUser->scenes()->create([
            Scene::LABEL => 'Second user scene',
            Scene::ACTIONS => ['20;turnOff'],
        ]);

        $firstThermostat = $firstUser->thermostats()->create([
            Thermostat::LABEL => 'First thermostat',
        ]);
        $secondThermostat = $secondUser->thermostats()->create([
            Thermostat::LABEL => 'Second thermostat',
        ]);
        $firstProfile = $firstThermostat->profiles()->make([
            ThermostatProfile::NAME => 'First profile',
            ThermostatProfile::ROOMS_CONFIG => [['room' => 'lounge']],
            ThermostatProfile::ACTIVE_ON => [],
        ]);
        $firstProfile->userId = $firstUser->id;
        $firstProfile->save();
        $secondProfile = $secondThermostat->profiles()->make([
            ThermostatProfile::NAME => 'Second profile',
            ThermostatProfile::ROOMS_CONFIG => [['room' => 'bedroom']],
            ThermostatProfile::ACTIVE_ON => [],
        ]);
        $secondProfile->userId = $secondUser->id;
        $secondProfile->save();

        $firstState = $firstUser->stateLogs()->create([
            StateLogEntry::CHANNEL_ID => 101,
            StateLogEntry::STATE => ['on' => true],
        ]);
        $secondState = $secondUser->stateLogs()->create([
            StateLogEntry::CHANNEL_ID => 202,
            StateLogEntry::STATE => ['on' => false],
        ]);

        $this->assertSame($firstUser->id, $firstScene->userId);
        $this->assertSame($secondUser->id, $secondScene->userId);
        $this->assertSame($firstUser->id, $firstProfile->userId);
        $this->assertSame($firstThermostat->id, $firstProfile->thermostatId);
        $this->assertSame($secondUser->id, $secondProfile->userId);
        $this->assertSame($secondThermostat->id, $secondProfile->thermostatId);
        $this->assertSame($firstUser->id, $firstState->userId);
        $this->assertSame($secondUser->id, $secondState->userId);

        $this->assertSame([(string) $firstScene->id], $firstUser->scenes()->pluck(Scene::ID)->all());
        $this->assertSame([(string) $secondScene->id], $secondUser->scenes()->pluck(Scene::ID)->all());
        $this->assertNull($firstUser->scenes()->find($secondScene->id));
        $this->assertNull($secondUser->scenes()->find($firstScene->id));
        $this->assertSame([(string) $firstProfile->id], $firstThermostat->profiles()->pluck(ThermostatProfile::ID)->all());
        $this->assertSame([(string) $secondProfile->id], $secondThermostat->profiles()->pluck(ThermostatProfile::ID)->all());
        $this->assertNull($firstThermostat->profiles()->find($secondProfile->id));
        $this->assertNull($secondThermostat->profiles()->find($firstProfile->id));
        $this->assertSame([(string) $firstState->id], $firstUser->stateLogs()->pluck(StateLogEntry::ID)->all());
        $this->assertSame([(string) $secondState->id], $secondUser->stateLogs()->pluck(StateLogEntry::ID)->all());
        $this->assertNull($firstUser->stateLogs()->find($secondState->id));
        $this->assertNull($secondUser->stateLogs()->find($firstState->id));
    }
}
