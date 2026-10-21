<?php

namespace suplascripts\tests\database;

use Illuminate\Database\QueryException;
use suplascripts\models\scene\Scene;

class MigrationTest extends DatabaseTestCase {

    public function testEveryApplicationMigrationRunsOnAnEmptyDatabase() {
        $expectedVersions = [];
        foreach (glob(__DIR__ . '/../../database/migrations/*.php') as $migration) {
            if (preg_match('/^(\d+)_/', basename($migration), $matches)) {
                $expectedVersions[] = (int) $matches[1];
            }
        }
        sort($expectedVersions);
        $actualVersions = array_map('intval', $this->db->getConnection()->table('migrations')->pluck('version')->all());
        sort($actualVersions);
        $this->assertSame($expectedVersions, $actualVersions);
        foreach (['users', 'scenes', 'thermostats', 'thermostat_profiles', 'state_logs'] as $table) {
            $this->assertTrue($this->db->getConnection()->getSchemaBuilder()->hasTable($table), $table);
        }
        $this->assertTrue($this->db->getConnection()->getSchemaBuilder()->hasColumn('scenes', 'actionTriggers'));
    }

    public function testDatabaseRejectsScenesWithMissingOwner() {
        $scene = new Scene(['label' => 'Orphan', 'actions' => ['1;turnOn']]);
        $scene->userId = '00000000-0000-4000-8000-000000000000';

        try {
            $scene->save();
            $this->fail('The migrated schema must enforce scene ownership.');
        } catch (QueryException $exception) {
            $this->assertSame('23000', $exception->errorInfo[0]);
            $this->assertSame(1452, $exception->errorInfo[1]);
        }
    }
}
