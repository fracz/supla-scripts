<?php

namespace suplascripts\app;

use FileSystemCache;
use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Slim\App;
use suplascripts\app\authorization\JwtAndBasicAuthorizationMiddleware;
use suplascripts\database\EloquentExceptionHandler;
use suplascripts\models\Client;
use suplascripts\models\observers\ObserverRegisterer;
use suplascripts\models\User;

/**
 * @property-read \Slim\Http\Request $request
 * @property-read \Slim\Http\Response $response
 * @property-read Capsule $db
 * @property-read UserAndUrlAwareLogger $logger
 * @property-read MetricsCollector $metrics
 * @property-read \Slim\Collection $settings
 */
class Application extends App {

    const VAR_PATH = __DIR__ . '/../../var';
    const CONFIG_PATH = self::VAR_PATH . '/config/config.json';

    private static $instance;

    public function __construct() {
        if (self::$instance) {
            throw new \BadMethodCallException('Application can be instantiated only once. Use getInstance() instead.');
        }
        self::$instance = $this;
        $config = require __DIR__ . '/../settings.php';
        parent::__construct(['settings' => $config]);
        $this->configureServices();
        $this->add(new JwtAndBasicAuthorizationMiddleware());
        ObserverRegisterer::registerModelObservers();
    }

    private function configureServices() {
        $this->configureDb();
        $this->configureLogger();
        $this->configureMetrics();
        FileSystemCache::$cacheDir = self::VAR_PATH . '/cache';
    }

    private function configureDb() {
        $this->getContainer()['db'] = function ($container) {
            $capsule = new Capsule;
            $dbSettings = $container['settings']['db'];
            $dbSettings['timezone'] = '+00:00';
            $dbSettings['charset'] = 'utf8';
            $dbSettings['collation'] = 'utf8_unicode_ci';
            $capsule->addConnection($dbSettings);
            $capsule->getContainer()->bind(ExceptionHandler::class, EloquentExceptionHandler::class);
            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->bootEloquent();
            $capsule->setAsGlobal();
            return $capsule;
        };
        /* get db from container to properly initialize before first use of Model elements*/
        $this->getContainer()->get('db');
    }

    private function configureLogger() {
        $this->getContainer()['logger'] = function () {
            return new UserAndUrlAwareLogger();
        };
    }

    private function configureMetrics() {
        $this->getContainer()['metrics'] = function () {
            $metricsSettings = $this->getSetting('metrics', []);
            $enabled = $metricsSettings['enabled'] ?? false;
            $address = 'udp://' . ($metricsSettings['host'] ?? 'suplascripts-metrics');
            $instanceName = $metricsSettings['instanceName'] ?? 'root';
            $port = $metricsSettings['statsd_port'] ?? 8125;
            return new MetricsCollector($enabled, $instanceName, ['default' => ['address' => $address, 'port' => $port]]);
        };
    }

    public static function getInstance(): Application {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get($property) {
        return $this->getContainer()->get($property);
    }

    public function getSetting($name, $default = null) {
        return $this->settings->get($name, $default);
    }

    /**
     * @return User|null
     */
    public function getCurrentUser() {
        if ($this->getContainer()->has('currentUser')) {
            return $this->currentUser;
        } elseif ($this->getContainer()->has('currentToken')) {
            $token = $this->currentToken;
            if (isset($token->user)) {
                $user = User::find($token->user->id);
            } elseif (isset($token->client)) {
                /** @var Client $client */
                $client = Client::find($token->client->id);
                if ($client && $client->active) {
                    $client->updateLastConnectionDate();
                    $client->save();
                    $this->getContainer()['currentClient'] = $client;
                    $user = $client->user;
                }
            }
            if ($user) {
                $this->getContainer()['currentUser'] = $user;
                return $this->getCurrentUser();
            }
        }
        return null;
    }

    public static function version(): string {
        $version = @file_get_contents(self::VAR_PATH . '/system/version');
        return $version ?: 'UNKNOWN';
    }
}
