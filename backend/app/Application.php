<?php

namespace suplascripts\app;

use Illuminate\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use M6Web\Component\Statsd\Client;
use Psr\Log\LoggerInterface;
use Slim\App;
use suplascripts\database\EloquentExceptionHandler;
use suplascripts\middlewares\JwtCheckerMiddleware;
use suplascripts\models\observers\ObserverRegisterer;
use suplascripts\models\User;

/**
 * @property-read \Slim\Http\Request $request
 * @property-read \Slim\Http\Response $response
 * @property-read Capsule $db
 * @property-read LoggerInterface $logger
 * @property-read \stdClass $currentToken
 * @property-read \Slim\Collection $settings
 */
class Application extends App
{
    const VAR_PATH = __DIR__ . '/../var';
    const CONFIG_PATH = __DIR__ . '/../../config.json';

    private static $instance;

    public function __construct(array $config = null)
    {
        if (self::$instance) {
            throw new \BadMethodCallException('Application can be instantiated only once. Use getInstance() instead.');
        }
        self::$instance = $this;
        if (!$config) {
            $config = require __DIR__ . '/../settings.php';
        }
        parent::__construct(['settings' => $config]);
        $this->configureServices();
        $this->add(new JwtCheckerMiddleware());
        ObserverRegisterer::registerModelObservers();
    }

    private function configureServices()
    {
        $this->configureDb();
        $this->configureLogger();
    }

    private function configureDb()
    {
        $this->getContainer()['db'] = function ($container) {
            $capsule = new Capsule;
            $capsule->addConnection($container['settings']['db']);
            $capsule->getContainer()->bind(ExceptionHandler::class, EloquentExceptionHandler::class);
            $capsule->setEventDispatcher(new Dispatcher(new Container));
            $capsule->bootEloquent();
            $capsule->setAsGlobal();
            return $capsule;
        };
        /* get db from container to properly initialize before first use of Model elements*/
        $this->getContainer()->get('db');
    }

    private function configureLogger()
    {
        $this->getContainer()['logger'] = function () {
            return new UserAndUrlAwareLogger();
        };
    }

    public static function getInstance(): Application
    {
        return self::$instance;
    }

    public function __get($property)
    {
        return $this->getContainer()->get($property);
    }

    public function getSetting($name)
    {
        return $this->settings->get($name);
    }

    /**
     * @return User
     */
    public function getCurrentUser()
    {
        if ($this->getContainer()->has('currentToken')) {
            $token = $this->currentToken;
            if (isset($token->user)) {
                return User::find($token->user->id);
            }
        }
        return null;
    }

    public static function version(): string
    {
        $version = @file_get_contents(self::VAR_PATH . '/system/version');
        return $version ?: 'UNKNOWN';
    }
}
