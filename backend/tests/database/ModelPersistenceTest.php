<?php

namespace suplascripts\tests\database;

use suplascripts\models\scene\Scene;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\thermostat\ThermostatProfile;
use suplascripts\models\User;

class ModelPersistenceTest extends DatabaseTestCase {

    public function testSceneJsonFieldsAreNormalizedAndPersistedAcrossReloads() {
        $user = $this->createUser();
        $scene = new Scene([
            Scene::LABEL => 'Persistence scene',
            Scene::ACTIONS => ['12;turnOn'],
            Scene::VOICE_TRIGGERS => ['  HELLO  ', 'hello', 'Goodbye'],
            Scene::NOTIFICATIONS => [['message' => 'Done']],
            Scene::ACTION_TRIGGERS => [['channelId' => 12, 'trigger' => 'on']],
            Scene::ENABLED => false,
        ]);
        $scene->userId = $user->id;
        $scene->createdAt = new \DateTime('2024-02-03 04:05:06', new \DateTimeZone('UTC'));
        $scene->save();

        $id = $scene->id;
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $id);
        $this->assertSame(['hello', 'goodbye'], $scene->voiceTriggers);
        $raw = $this->db->getConnection()->table(Scene::TABLE_NAME)->where('id', $id)->first();
        $this->assertSame(json_encode(['12;turnOn']), $raw->actions);
        $this->assertSame(json_encode(['hello', 'goodbye']), $raw->voiceTriggers);
        $this->assertSame(json_encode([['message' => 'Done']]), $raw->notifications);
        $this->assertSame(json_encode([['channelId' => 12, 'trigger' => 'on']]), $raw->actionTriggers);
        $this->assertSame('2024-02-03 04:05:06', (new \DateTime($raw->createdAt, new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'));
        $this->assertSame('2024-02-03T04:05:06+00:00', $scene->toArray()['createdAt']);

        $reloaded = Scene::find($id);
        $this->assertSame(['12;turnOn'], $reloaded->actions);
        $this->assertSame(['hello', 'goodbye'], $reloaded->voiceTriggers);
        $this->assertSame([['message' => 'Done']], $reloaded->notifications);
        $this->assertSame([['channelId' => 12, 'trigger' => 'on']], $reloaded->actionTriggers);

        $reloaded->actions = ['12;turnOff'];
        $reloaded->timestamps = false;
        $reloaded->updatedAt = new \DateTime('2024-02-04 05:06:07', new \DateTimeZone('UTC'));
        $reloaded->save();
        $this->assertSame(['12;turnOff'], Scene::find($id)->actions);
        $updatedRaw = $this->db->getConnection()->table(Scene::TABLE_NAME)->where('id', $id)->first();
        $this->assertSame(json_encode(['12;turnOff']), $updatedRaw->actions);
        $this->assertSame('2024-02-04 05:06:07', (new \DateTime($updatedRaw->updatedAt, new \DateTimeZone('UTC')))->format('Y-m-d H:i:s'));
        $this->assertSame('2024-02-04T05:06:07+00:00', Scene::find($id)->toArray()['updatedAt']);
    }

    public function testUserSettingsAndEncryptedCredentialsRoundTrip() {
        $user = $this->createUser();
        $user->timezone = 'America/Los_Angeles';
        $user->sceneLimit = 37;
        $apiCredentials = ['server' => 'api.example.test', 'access_token' => 'private-api-token'];
        $apiCredentials['clientSettings'] = ['locale' => 'en', 'theme' => 'dark'];
        $user->apiCredentials = json_encode($apiCredentials);
        $user->pushoverCredentials = json_encode(['user' => 'private-user', 'token' => 'private-pushover-token']);
        $user->webhookToken = 'private-webhook-token';
        $user->save();

        $raw = $this->db->getConnection()->table(User::TABLE_NAME)->where('id', $user->id)->first();
        $this->assertSame('America/Los_Angeles', $raw->timezone);
        $this->assertSame(37, (int)$raw->sceneLimit);
        $this->assertNotSame(json_encode($apiCredentials), $raw->apiCredentials);
        $this->assertNotSame(json_encode(['user' => 'private-user', 'token' => 'private-pushover-token']), $raw->pushoverCredentials);
        $this->assertNotSame('private-webhook-token', $raw->webhookToken);
        foreach (['apiCredentials', 'pushoverCredentials', 'webhookToken'] as $column) {
            $this->assertStringNotContainsString('private-', $raw->$column);
        }

        $reloaded = User::find($user->id);
        $this->assertSame('America/Los_Angeles', $reloaded->timezone);
        $this->assertSame(37, (int)$reloaded->sceneLimit);
        $this->assertSame(json_encode($apiCredentials), $reloaded->apiCredentials);
        $this->assertSame(['locale' => 'en', 'theme' => 'dark'], $reloaded->getApiCredentials()['clientSettings']);
        $this->assertSame(json_encode(['user' => 'private-user', 'token' => 'private-pushover-token']), $reloaded->pushoverCredentials);
        $this->assertSame('private-webhook-token', $reloaded->webhookToken);

        $reloaded->pushoverCredentials = null;
        $reloaded->save();
        $this->assertNull($this->db->getConnection()->table(User::TABLE_NAME)->where('id', $user->id)->value('pushoverCredentials'));
        $this->assertNull(User::find($user->id)->pushoverCredentials);

        $updatedCredentials = [
            'server' => 'api.example.test',
            'access_token' => 'updated-token',
            'clientSettings' => ['theme' => 'light'],
        ];
        $reloaded->apiCredentials = json_encode($updatedCredentials);
        $reloaded->save();
        $this->assertSame(['theme' => 'light'], User::find($user->id)->getApiCredentials()['clientSettings']);
    }

    public function testThermostatProfileNestedSchedulesRoundTrip() {
        $user = $this->createUser();
        $thermostat = new Thermostat([
            Thermostat::LABEL => 'Persistence thermostat',
            Thermostat::TARGET => 'temperature',
        ]);
        $thermostat->userId = $user->id;
        $thermostat->save();
        $profile = $thermostat->profiles()->make([
            ThermostatProfile::NAME => 'Weekday',
            ThermostatProfile::ROOMS_CONFIG => [
                'living-room' => ['heatFrom' => 18, 'heatTo' => 21],
                'bedroom' => ['heatFrom' => 16, 'heatTo' => 19],
            ],
            ThermostatProfile::ACTIVE_ON => [
                ['weekdays' => [1, 2, 3, 4, 5], 'timeRange' => ['timeStart' => '06:30', 'timeEnd' => '08:00']],
                ['weekdays' => [0, 6], 'timeRange' => ['timeStart' => '09:00', 'timeEnd' => '10:30']],
            ],
        ]);
        $profile->userId = $user->id;
        $profile->save();

        $raw = $this->db->getConnection()->table(ThermostatProfile::TABLE_NAME)->where('id', $profile->id)->first();
        $this->assertSame(json_encode($profile->roomsConfig), $raw->roomsConfig);
        $this->assertSame(json_encode($profile->activeOn), $raw->activeOn);
        $reloaded = ThermostatProfile::find($profile->id);
        $this->assertSame($profile->roomsConfig, $reloaded->roomsConfig);
        $this->assertSame($profile->activeOn, $reloaded->activeOn);

        $updatedSchedule = [['weekdays' => [2], 'timeRange' => ['timeStart' => '07:00', 'timeEnd' => '08:00']]];
        $reloaded->activeOn = $updatedSchedule;
        $reloaded->save();
        $this->assertSame($updatedSchedule, ThermostatProfile::find($profile->id)->activeOn);
        $rawSchedule = $this->db->getConnection()->table(ThermostatProfile::TABLE_NAME)
            ->where('id', $profile->id)
            ->value('activeOn');
        $this->assertSame(json_encode($updatedSchedule), $rawSchedule);
    }
}
