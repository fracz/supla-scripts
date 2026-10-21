<?php

namespace suplascripts\tests\database;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use suplascripts\models\scene\Scene;
use suplascripts\models\User;

class ModelDatesTest extends DatabaseTestCase {

    private $previousTestNow;

    protected function setUp(): void {
        parent::setUp();
        $this->previousTestNow = Carbon::getTestNow();
    }

    protected function tearDown(): void {
        try {
            Carbon::setTestNow($this->previousTestNow);
        } finally {
            parent::tearDown();
        }
    }

    public function testTimestampsAreAssignedOnSaveAndUpdateOnlyAdvancesUpdatedAt() {
        Carbon::setTestNow(Carbon::parse('2024-05-06 07:08:09', 'UTC'));
        $user = $this->createUser();

        $created = User::find($user->id);
        $this->assertSame('2024-05-06T07:08:09+00:00', $created->toArray()['createdAt']);
        $this->assertSame('2024-05-06T07:08:09+00:00', $created->toArray()['updatedAt']);

        Carbon::setTestNow(Carbon::parse('2024-05-06 07:09:10', 'UTC'));
        $created->timezone = 'America/Los_Angeles';
        $created->save();

        $updated = User::find($user->id);
        $this->assertSame('2024-05-06T07:08:09+00:00', $updated->toArray()['createdAt']);
        $this->assertSame('2024-05-06T07:09:10+00:00', $updated->toArray()['updatedAt']);
    }

    public function testNullableSceneDateFieldsRemainNullAfterSaveAndReload() {
        $user = $this->createUser();
        $scene = new Scene([
            Scene::LABEL => 'Nullable dates',
            Scene::ACTIONS => ['12;turnOn'],
        ]);
        $scene->lastUsed = null;
        $scene->nextExecutionTime = null;
        $scene->userId = $user->id;
        $scene->save();

        $reloaded = Scene::find($scene->id);
        $this->assertNull($reloaded->lastUsed);
        $this->assertNull($reloaded->nextExecutionTime);
        $this->assertNull($reloaded->toArray()[Scene::LAST_USED]);
        $this->assertNull($reloaded->toArray()[Scene::NEXT_EXECUTION_TIME]);
    }

    public function testOffsetDateTimePersistsAsUtcInstantAndSerializesCorrectly() {
        $user = $this->createUser();
        $instant = new DateTime('2024-05-06 12:34:56+05:30');
        $scene = new Scene([
            Scene::LABEL => 'Offset date',
            Scene::ACTIONS => ['12;turnOn'],
        ]);
        $scene->lastUsed = $instant;
        $scene->userId = $user->id;
        $scene->save();

        $raw = $this->db->getConnection()->table(Scene::TABLE_NAME)->where('id', $scene->id)->value(Scene::LAST_USED);
        $this->assertSame('2024-05-06 07:04:56', $raw);

        $reloaded = Scene::find($scene->id);
        $this->assertSame('2024-05-06T07:04:56+00:00', $reloaded->toArray()[Scene::LAST_USED]);
        $this->assertSame('2024-05-06T07:04:56+00:00', $reloaded->lastUsed->setTimezone(new DateTimeZone('UTC'))->format('c'));
    }
}
