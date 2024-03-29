<?php

namespace suplascripts;

use Monolog\ErrorHandler;
use Monolog\Logger;
use suplascripts\app\Application;
use suplascripts\app\UserAndUrlAwareLogger;
use suplascripts\controllers\ChannelsController;
use suplascripts\controllers\ClientsController;
use suplascripts\controllers\DevicesController;
use suplascripts\controllers\LogsController;
use suplascripts\controllers\NotificationsController;
use suplascripts\controllers\SceneGroupsController;
use suplascripts\controllers\ScenesController;
use suplascripts\controllers\StateLogsController;
use suplascripts\controllers\StateWebhookController;
use suplascripts\controllers\SystemController;
use suplascripts\controllers\thermostat\ThermostatProfilesController;
use suplascripts\controllers\thermostat\ThermostatRoomsController;
use suplascripts\controllers\thermostat\ThermostatsController;
use suplascripts\controllers\TokensController;
use suplascripts\controllers\UsersController;
use suplascripts\controllers\VoiceCommandsController;

require __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 'Off');
ini_set("log_errors", 1);
ErrorHandler::register(new UserAndUrlAwareLogger(Logger::NOTICE, 'error'));
$startTime = microtime(true);

$app = new Application();
$app->group('/api', function () use ($app) {
    $app->get('/info', SystemController::class . ':getInfo');
    $app->group('/tokens', function () use ($app) {
        $app->post('/new', TokensController::class . ':oauthAuthenticate');
        $app->patch('/personal', TokensController::class . ':checkPersonalAccessToken');
        $app->post('/client', TokensController::class . ':createTokenForClient');
        $app->put('', TokensController::class . ':refreshToken');
    });
    $app->group('/users', function () use ($app) {
        $app->post('/register', UsersController::class . ':post');
        $app->get('/{id}', UsersController::class . ':get');
        $app->patch('/{id}', UsersController::class . ':patch');
        $app->put('/{id}', UsersController::class . ':put');
    });
    $app->group('/devices', function () use ($app) {
        $app->get('', DevicesController::class . ':getList');
    });
    $app->group('/channels', function () use ($app) {
        $app->get('/{id}', ChannelsController::class . ':get');
        $app->get('/{id}/logs', ChannelsController::class . ':getSensorLogs');
        $app->patch('/{id}', ChannelsController::class . ':execute');
    });
    $app->group('/logs', function () use ($app) {
        $app->get('', LogsController::class . ':getLatest');
    });

    $app->group('/thermostats', function () use ($app) {
        $app->get('', ThermostatsController::class . ':getList');
        $app->post('', ThermostatsController::class . ':post');
        $app->get('/{id}', ThermostatsController::class . ':get');
        $app->delete('/{id}', ThermostatsController::class . ':delete');
        $app->patch('/{id}', ThermostatsController::class . ':patch');
        $app->get('/preview/{slug}', ThermostatsController::class . ':getBySlug');
        $app->patch('/preview/{slug}/{id}', ThermostatsController::class . ':patch');
    });
    $app->group('/thermostats/{thermostatId}/thermostat-rooms', function () use ($app) {
        $app->get('', ThermostatRoomsController::class . ':getList');
        $app->post('', ThermostatRoomsController::class . ':post');
        $app->put('/{roomId}', ThermostatRoomsController::class . ':put');
        $app->delete('/{roomId}', ThermostatRoomsController::class . ':delete');
    });
    $app->group('/thermostats/{thermostatId}/thermostat-profiles', function () use ($app) {
        $app->get('', ThermostatProfilesController::class . ':getList');
        $app->post('', ThermostatProfilesController::class . ':post');
        $app->put('/{id}', ThermostatProfilesController::class . ':put');
        $app->delete('/{id}', ThermostatProfilesController::class . ':delete');
    });

    $app->group('/voice-commands', function () use ($app) {
        $app->patch('', VoiceCommandsController::class . ':executeVoiceCommand');
        $app->get('/last', VoiceCommandsController::class . ':getLastVoiceCommand');
    });

    $app->group('/scenes', function () use ($app) {
        $app->get('', ScenesController::class . ':getList');
        $app->post('', ScenesController::class . ':post');
        $app->get('/public/{slug}', ScenesController::class . ':executeSceneBySlug');
        $app->get('/execute/{id}', ScenesController::class . ':executeSceneByClient');
        $app->get('/{id}', ScenesController::class . ':get');
        $app->put('/{id}', ScenesController::class . ':put');
        $app->delete('/{id}', ScenesController::class . ':delete');
        $app->delete('/{id}/pending', ScenesController::class . ':deletePending');
        $app->patch('/feedback', ScenesController::class . ':interpolateFeedback');
        $app->patch('/{id}', ScenesController::class . ':executeScene');
        $app->post('/{id}/tokens', ScenesController::class . ':createClientForScene');
    });

    $app->group('/scene-groups', function () use ($app) {
        $app->get('', SceneGroupsController::class . ':getList');
        $app->post('', SceneGroupsController::class . ':post');
        $app->patch('', SceneGroupsController::class . ':updateOrder');
        $app->put('/{id}', SceneGroupsController::class . ':put');
        $app->delete('/{id}', SceneGroupsController::class . ':delete');
    });

    $app->group('/notifications', function () use ($app) {
        $app->get('', NotificationsController::class . ':getList');
        $app->post('', NotificationsController::class . ':post');
        $app->get('/{id}', NotificationsController::class . ':get');
        $app->put('/{id}', NotificationsController::class . ':put');
        $app->patch('/{id}', NotificationsController::class . ':executeAction');
        $app->delete('/{id}', NotificationsController::class . ':delete');
    });

    $app->group('/clients', function () use ($app) {
        $app->get('', ClientsController::class . ':getList');
        $app->post('', ClientsController::class . ':post');
        $app->post('/registration-codes', ClientsController::class . ':postRegistrationCode');
        $app->get('/{id}', ClientsController::class . ':get');
        $app->put('/{id}', ClientsController::class . ':put');
        $app->delete('/{id}', ClientsController::class . ':delete');
    });

    $app->group('/state-webhook', function () use ($app) {
        $app->post('', StateWebhookController::class . ':post');
        $app->put('', StateWebhookController::class . ':put');
    });

    $app->group('/state-logs', function () use ($app) {
        $app->get('', StateLogsController::class . ':getLatest');
    });
});
$app->run();

$elapsedTime = round((microtime(true) - $startTime) * 1000);

$app->metrics->timing('time', $elapsedTime);
$app->metrics->increment('api_hit');
$app->metrics->send();
