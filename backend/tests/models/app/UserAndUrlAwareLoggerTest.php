<?php

namespace suplascripts\tests\models\app;

use PHPUnit\Framework\TestCase;
use suplascripts\app\Application;
use suplascripts\app\UserAndUrlAwareLogger;

class UserAndUrlAwareLoggerTest extends TestCase {

    private $logDirectory;
    private $filename;
    private $logFiles = [];

    protected function setUp(): void {
        $this->logDirectory = Application::VAR_PATH . '/logs';
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true);
        }
        $this->filename = 'logger-container-unavailable-' . uniqid();
    }

    protected function tearDown(): void {
        foreach ($this->logFiles as $logFile) {
            if (is_file($logFile)) {
                unlink($logFile);
            }
        }
        if (is_dir($this->logDirectory) && count(scandir($this->logDirectory)) === 2) {
            rmdir($this->logDirectory);
        }
    }

    public function testLogsMessageAndContextWhenApplicationContainerIsUnavailable() {
        $app = new class extends Application {
            public function __construct() {
            }

            public function getContainer() {
                return null;
            }
        };
        $logger = new class($app, $this->filename) extends UserAndUrlAwareLogger {
            private $app;

            public function __construct(Application $app, string $filename) {
                $this->app = $app;
                parent::__construct(\Monolog\Logger::ERROR, $filename);
            }

            protected function getApp(): Application {
                return $this->app;
            }
        };

        $logger->error('PHP deprecation during application startup', ['source' => 'startup']);

        $this->logFiles = glob($this->logDirectory . '/' . $this->filename . '-*.log');
        $this->assertCount(1, $this->logFiles);
        $log = file_get_contents($this->logFiles[0]);
        $this->assertStringContainsString('PHP deprecation during application startup', $log);
        $this->assertStringContainsString('"source":"startup"', $log);
    }

    public function testDoesNotResolveUserFromTokenWhileLogging() {
        $container = $this->containerWith([
            'currentToken' => (object) ['user' => (object) ['id' => 42]],
            'request' => new class {
                public function getUri() {
                    return 'https://example.test/api/scenes';
                }
            },
        ]);
        $app = $this->applicationWithUnresolvedUser($container);
        $logger = $this->loggerFor($app);

        $logger->error('Token-backed log entry');

        $this->assertLogContains(['url' => 'https://example.test/api/scenes', 'userId' => 42]);
    }

    public function testUsesAlreadyResolvedUserFromContainer() {
        $request = new class {
            public $onGetUri;

            public function getUri() {
                if ($this->onGetUri) {
                    $callback = $this->onGetUri;
                    $this->onGetUri = null;
                    $callback();
                }
                return 'https://example.test/api/scenes';
            }
        };
        $container = $this->containerWith([
            'currentUser' => (object) ['id' => 17],
            'request' => $request,
        ]);
        $app = $this->applicationWithUnresolvedUser($container);
        $logger = $this->loggerFor($app);
        $request->onGetUri = function () use ($logger) {
            $logger->error('Reentrant request log');
        };

        $logger->error('Cached-user log entry');

        $this->assertLogContains(['url' => 'https://example.test/api/scenes', 'userId' => 17]);
        $log = file_get_contents($this->logFiles[0]);
        $this->assertStringContainsString('Reentrant request log', $log);
        $this->assertStringContainsString('Cached-user log entry', $log);
    }

    private function containerWith(array $values) {
        return new class($values) {
            private $values;

            public function __construct(array $values) {
                $this->values = $values;
            }

            public function has(string $key): bool {
                return array_key_exists($key, $this->values);
            }

            public function get(string $key) {
                return $this->values[$key];
            }
        };
    }

    private function applicationWithUnresolvedUser($container): Application {
        return new class($container) extends Application {
            private $container;

            public function __construct($container) {
                $this->container = $container;
            }

            public function getContainer() {
                return $this->container;
            }

            public function getCurrentUser() {
                throw new \LogicException('Logger must not resolve the current user');
            }
        };
    }

    private function loggerFor(Application $app): UserAndUrlAwareLogger {
        return new class($app, $this->filename) extends UserAndUrlAwareLogger {
            private $app;

            public function __construct(Application $app, string $filename) {
                $this->app = $app;
                parent::__construct(\Monolog\Logger::ERROR, $filename);
            }

            protected function getApp(): Application {
                return $this->app;
            }
        };
    }

    private function assertLogContains(array $context) {
        $this->logFiles = glob($this->logDirectory . '/' . $this->filename . '-*.log');
        $this->assertCount(1, $this->logFiles);
        $log = file_get_contents($this->logFiles[0]);
        foreach ($context as $key => $value) {
            $this->assertStringContainsString(
                '"' . $key . '":' . json_encode($value, JSON_UNESCAPED_SLASHES),
                $log
            );
        }
    }
}
