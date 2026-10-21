<?php

namespace suplascripts\tests\database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use suplascripts\models\User;

abstract class DatabaseTestCase extends TestCase {

    protected $db;
    private $previousResolver;

    protected function setUp(): void {
        $this->previousResolver = Model::getConnectionResolver();
        $this->db = new Capsule();
        $this->db->addConnection($GLOBALS['testDatabaseSettings']);
        $this->db->bootEloquent();
        $this->db->getConnection()->beginTransaction();
        // A prior test's fixtures must not survive its transaction rollback.
        $this->assertSame(0, $this->db->getConnection()->table('users')->count());
    }

    protected function tearDown(): void {
        $this->db->getConnection()->rollBack();
        $this->db->getConnection()->disconnect();
        if ($this->previousResolver) {
            Model::setConnectionResolver($this->previousResolver);
        } else {
            Model::unsetConnectionResolver();
        }
    }

    protected function createUser(array $attributes = []): User {
        $user = new User();
        foreach (array_merge([
            'username' => 'test-' . bin2hex(random_bytes(6)),
            'password' => password_hash('disposable-test-password', PASSWORD_DEFAULT),
            'apiCredentials' => json_encode(['server' => 'example.invalid', 'accessToken' => 'test-token']),
            'timezone' => 'Europe/Warsaw',
        ], $attributes) as $key => $value) {
            $user->$key = $value;
        }
        $user->save();
        return $user;
    }
}
