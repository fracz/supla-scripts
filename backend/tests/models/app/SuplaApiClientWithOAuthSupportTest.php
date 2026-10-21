<?php

namespace suplascripts\tests\models\app;

use PHPUnit\Framework\TestCase;
use suplascripts\app\Application;
use suplascripts\app\MetricsCollector;
use suplascripts\models\supla\SuplaApiClientWithOAuthSupport;

class SuplaApiClientWithOAuthSupportTest extends TestCase {

    private $instanceProperty;
    private $previousInstance;
    private $serverProcess;
    private $serverPipes = [];

    protected function setUp(): void {
        $this->instanceProperty = new \ReflectionProperty(Application::class, 'instance');
        $this->instanceProperty->setAccessible(true);
        $this->previousInstance = $this->instanceProperty->getValue();
    }

    protected function tearDown(): void {
        $this->instanceProperty->setValue(null, $this->previousInstance);
        foreach ($this->serverPipes as $pipe) {
            fclose($pipe);
        }
        if (is_resource($this->serverProcess)) {
            proc_terminate($this->serverProcess);
            proc_close($this->serverProcess);
        }
    }

    public function testOAuthProfileGetWithNullPayloadReachesTransport() {
        $client = $this->client();

        $this->assertFalse($client->remoteRequest(null, '/api/users/current', 'GET', true));
        $this->assertStringStartsWith('CURL ERR:', $client->getLastError());
    }

    public function testInheritedDeviceGetWithNullPayloadReachesTransport() {
        $client = $this->client();

        $this->assertFalse($client->ioDevices());
        $this->assertStringStartsWith('CURL ERR:', $client->getLastError());
    }

    /** @dataProvider getRequests */
    public function testGetReturnsProfileWithoutChangingTheRoute($data, string $path, string $expectedUri) {
        $this->serverProcess = proc_open([
            PHP_BINARY,
            __DIR__ . '/../../support/api-get-server.php',
        ], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $this->serverPipes);
        $this->assertIsResource($this->serverProcess);
        stream_set_timeout($this->serverPipes[1], 5);
        $address = trim(fgets($this->serverPipes[1]));
        $this->assertMatchesRegularExpression('/^127\.0\.0\.1:\d+$/', $address);
        $client = $this->client('http://' . $address);

        $profile = $client->remoteRequest($data, $path, 'GET', true);

        $this->assertIsObject($profile, (string) $client->getLastError());
        $this->assertSame('oauth@example.test', $profile->email);
        $this->assertSame($expectedUri, $profile->uri);
        $this->assertSame('Bearer test-token', $profile->authorization);
    }

    public static function getRequests(): array {
        return [
            'OAuth profile' => [null, '/api/users/current', '/api/users/current'],
            'empty payload' => [[], '/api/users/current', '/api/users/current'],
            'existing query' => [null, '/api/users/current?include=state', '/api/users/current?include=state'],
            'query payload' => [['page' => 2], '/api/users/current?include=state', '/api/users/current?include=state&page=2'],
        ];
    }

    private function client(?string $server = null): SuplaApiClientWithOAuthSupport {
        $metrics = $this->createMock(MetricsCollector::class);
        $metrics->expects($this->once())->method('increment')->with('api_call');
        $app = new class($metrics) extends Application {
            private $metrics;

            public function __construct(MetricsCollector $metrics) {
                $this->metrics = $metrics;
            }

            public function __get($property) {
                return $this->metrics;
            }
        };
        $this->instanceProperty->setValue(null, $app);

        return new class($server) extends SuplaApiClientWithOAuthSupport {
            public function __construct(?string $server) {
                parent::__construct(null, [
                    'target_url' => 'http://unused.invalid',
                    'access_token' => 'test-token',
                ], false);
                // Exercise the real GET transport and cURL without network access.
                // A path below a regular file cannot exist, so transport fails deterministically.
                $this->server = $server ?? 'file://' . __FILE__;
            }
        };
    }
}
