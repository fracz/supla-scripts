<?php

namespace suplascripts\tests\database;

use Firebase\JWT\JWT;
use suplascripts\app\Application;
use suplascripts\models\User;

class HttpSmokeTest extends DatabaseTestCase {

    private $server;
    private $serverLog;
    private $baseUrl;
    private $jwtKey;
    private $fixtureIds = [];

    protected function setUp(): void {
        parent::setUp();
        $this->jwtKey = bin2hex(random_bytes(32));
        $this->db->addConnection($GLOBALS['testDatabaseSettings'], 'http-fixtures');
        $this->serverLog = tempnam(sys_get_temp_dir(), 'supla-http-');
        if (file_exists(Application::CONFIG_PATH)) {
            throw new \RuntimeException('Refusing to overwrite application configuration.');
        }
        file_put_contents(Application::CONFIG_PATH, json_encode([
            'db' => $GLOBALS['testDatabaseSettings'],
            'jwt' => ['key' => $this->jwtKey],
            'metrics' => ['enabled' => false],
        ]));
        chmod(Application::CONFIG_PATH, 0600);

        try {
            $socket = stream_socket_server('tcp://127.0.0.1:0');
            if (!$socket) {
                throw new \RuntimeException('Cannot reserve an HTTP test port.');
            }
            $address = stream_socket_get_name($socket, false);
            fclose($socket);
            $this->baseUrl = 'http://' . $address;
            $this->server = proc_open(
                [PHP_BINARY, '-S', $address, '-t', dirname(__DIR__, 2), dirname(__DIR__, 2) . '/index.php'],
                [0 => ['file', '/dev/null', 'r'], 1 => ['file', $this->serverLog, 'a'], 2 => ['file', $this->serverLog, 'a']],
                $pipes
            );
            if (!is_resource($this->server)) {
                throw new \RuntimeException('Cannot start the HTTP test server.');
            }
            $deadline = microtime(true) + 5;
            do {
                $connection = @stream_socket_client('tcp://' . $address, $error, $message, 0.1);
                if ($connection) {
                    fclose($connection);
                    return;
                }
                if (!proc_get_status($this->server)['running']) {
                    break;
                }
                usleep(20000);
            } while (microtime(true) < $deadline);
            throw new \RuntimeException('HTTP server failed to start: ' . file_get_contents($this->serverLog));
        } catch (\Throwable $exception) {
            $this->tearDown();
            throw $exception;
        }
    }

    protected function tearDown(): void {
        try {
            if (is_resource($this->server)) {
                proc_terminate($this->server);
                proc_close($this->server);
            }
            $this->db->getConnection('http-fixtures')->table(User::TABLE_NAME)->whereIn('id', $this->fixtureIds)->delete();
            $this->db->getConnection('http-fixtures')->disconnect();
            if (is_file(Application::CONFIG_PATH)) {
                unlink(Application::CONFIG_PATH);
            }
            if ($this->serverLog && is_file($this->serverLog)) {
                unlink($this->serverLog);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function testApplicationBootsAndRejectsMissingAuthenticationOnRegisteredRoute() {
        $response = $this->request('/api/users/current');

        $this->assertSame(401, $response['status'], $response['body']);
        $this->assertSame(404, $this->request('/not-a-real-route')['status']);
    }

    public function testInvalidSignatureAndExpiredTokensAreRejected() {
        $user = $this->createHttpUser('http-token-user');
        $this->assertSame(401, $this->request('/api/users/current', $this->token($user, 'wrong-signing-key'))['status']);
        $this->assertSame(401, $this->request('/api/users/current', $this->token($user, null, true))['status']);
        $this->assertSame(401, $this->request('/api/users/current', 'not-a-jwt')['status']);
    }

    public function testValidTokenResolvesUserAndReturnsJsonWithoutStoredCredentials() {
        $user = $this->createHttpUser('http-current-user');
        $response = $this->request('/api/users/current', $this->token($user));

        $this->assertSame(200, $response['status'], $response['body']);
        $this->assertStringStartsWith('application/json', $response['contentType']);
        $data = json_decode($response['body'], true);
        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame((string)$user->id, $data['id']);
        $this->assertSame('http-current-user', $data['username']);
        $this->assertSame('Europe/Warsaw', $data['timezone']);
        foreach (['password', 'apiCredentials', 'pushoverCredentials', 'webhookToken', 'webhookRefreshToken'] as $field) {
            $this->assertArrayNotHasKey($field, $data);
        }
        $this->assertStringNotContainsString('private-http-', $response['body']);
    }

    public function testAuthenticatedUserCannotReadAnotherUsersAccount() {
        $first = $this->createHttpUser('http-first-user');
        $second = $this->createHttpUser('http-second-user');
        $response = $this->request('/api/users/' . $second->id, $this->token($first));

        $this->assertSame(403, $response['status'], $response['body']);
        $this->assertStringNotContainsString('http-second-user', $response['body']);
        $this->assertStringNotContainsString('private-http-', $response['body']);
    }

    public function testBasicAuthenticationFallbackAcceptsCorrectPasswordAndRejectsWrongPassword() {
        $user = $this->createHttpUser('http-basic-user');
        $response = $this->request('/api/users/current', null, [
            'Authorization: Basic ' . base64_encode('http-basic-user:private-http-password'),
        ]);

        $this->assertSame(200, $response['status'], $response['body']);
        $this->assertSame((string)$user->id, json_decode($response['body'], true)['id']);
        $this->assertSame(401, $this->request('/api/users/current', null, [
            'Authorization: Basic ' . base64_encode('http-basic-user:wrong-password'),
        ])['status']);
    }

    private function createHttpUser($username): User {
        // HTTP runs in a separate process: fixtures must be committed on a
        // separate connection, then explicitly removed in tearDown (FK cascade).
        $user = new User();
        $user->setConnection('http-fixtures');
        $user->username = $username;
        $user->password = password_hash('private-http-password', PASSWORD_DEFAULT);
        $user->apiCredentials = json_encode(['server' => 'example.invalid', 'access_token' => 'private-http-api']);
        $user->pushoverCredentials = 'private-http-pushover';
        $user->webhookToken = 'private-http-webhook';
        $user->timezone = 'Europe/Warsaw';
        // Token-backed accounts do not expose webhook refresh credentials.
        $user->tokenExpirationTime = '2030-01-01 00:00:00';
        $user->save();
        $this->fixtureIds[] = (string)$user->id;
        return $user;
    }

    private function token(User $user, $key = null, $expired = false): string {
        return JWT::encode([
            'user' => ['id' => (string)$user->id],
            'exp' => time() + ($expired ? -3600 : 3600),
        ], $key ?? $this->jwtKey, 'HS256');
    }

    private function request($path, $token = null, array $headers = []): array {
        $curl = curl_init($this->baseUrl . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_PROXY => '',
            CURLOPT_HTTPHEADER => $token === null ? $headers : ['Authorization: Bearer ' . $token],
        ]);
        try {
            $body = curl_exec($curl);
            if ($body === false) {
                throw new \RuntimeException('HTTP request failed: ' . curl_error($curl));
            }
            return [
                'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE),
                'contentType' => curl_getinfo($curl, CURLINFO_CONTENT_TYPE) ?: '',
                'body' => $body,
            ];
        } finally {
            curl_close($curl);
        }
    }
}
