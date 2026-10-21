<?php

namespace suplascripts\tests\database;

use PHPUnit\Framework\MockObject\MockObject;
use suplascripts\models\scene\Scene;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\thermostat\ThermostatProfileTimeSpan;
use suplascripts\tests\support\ApiSceneExecutor;

class PersistedExecutionTest extends DatabaseTestCase {

    public function testLoadedSceneExecutesPersistedCommandsThroughApiBoundary() {
        $user = $this->createUser();
        $scene = $user->scenes()->create([
            Scene::LABEL => 'Persisted actions',
            Scene::ACTIONS => ['11;turnOn|12;setRgb,#123456,70,30|13;turnOff'],
        ]);
        $loadedScene = Scene::findOrFail($scene->id);

        /** @var SuplaApi|MockObject $api */
        $api = $this->createMock(SuplaApi::class);
        $calls = [];
        $api->expects($this->exactly(3))->method('clearCache')
            ->willReturnCallback(function ($channelId) use (&$calls) {
                $calls[] = ['clearCache', [$channelId]];
            });
        $api->expects($this->once())->method('turnOn')->with(11)
            ->willReturnCallback(function ($channelId) use (&$calls) {
                $calls[] = ['turnOn', [$channelId]];
                return true;
            });
        $api->expects($this->once())->method('setRgb')->with(12, '#123456', 70, 30)
            ->willReturnCallback(function ($channelId, $color, $brightness, $saturation) use (&$calls) {
                $calls[] = ['setRgb', [$channelId, $color, $brightness, $saturation]];
                return true;
            });
        $api->expects($this->once())->method('turnOff')->with(13)
            ->willReturnCallback(function ($channelId) use (&$calls) {
                $calls[] = ['turnOff', [$channelId]];
                return false;
            });

        $results = (new ApiSceneExecutor($api))->executeCommandsFromString(
            $loadedScene->actions[0],
            $user
        );

        $this->assertSame([
            ['clearCache', ['11']], ['turnOn', [11]],
            ['clearCache', ['12']], ['setRgb', [12, '#123456', 70, 30]],
            ['clearCache', ['13']], ['turnOff', [13]],
        ], $calls);
        $this->assertSame([true, true, false], $results);
    }

    public function testLoadedThermostatProfileRetainsJsonScheduleForTimeSpanCalculation() {
        $user = $this->createUser();
        $thermostat = $user->thermostats()->create([
            Thermostat::LABEL => 'Schedule test',
        ]);
        $profile = $thermostat->profiles()->make([
            ThermostatProfile::NAME => 'Weekday morning',
            ThermostatProfile::ROOMS_CONFIG => [['heatFrom' => 18, 'heatTo' => 21]],
            ThermostatProfile::ACTIVE_ON => [[
                'weekdays' => [1],
                'timeRange' => ['timeStart' => '09:15', 'timeEnd' => '17:45'],
            ]],
        ]);
        $profile->userId = $user->id;
        $profile->save();

        $loadedProfile = ThermostatProfile::findOrFail($profile->id);
        $timeSpan = new ThermostatProfileTimeSpan($loadedProfile->activeOn[0]);
        $from = new \DateTime('2024-01-08 08:00:00', new \DateTimeZone('UTC'));

        $this->assertSame('2024-01-08 09:15:00 UTC', $timeSpan->getClosestStart($from)->format('Y-m-d H:i:s T'));
        $this->assertSame('2024-01-08 17:45:00 UTC', $timeSpan->getClosestEnd($from)->format('Y-m-d H:i:s T'));
    }
}
