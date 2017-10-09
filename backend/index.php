<?php

namespace suplascripts;

use suplascripts\app\Application;
use suplascripts\controllers\ChannelsController;
use suplascripts\controllers\DevicesController;
use suplascripts\controllers\LogsController;
use suplascripts\controllers\SystemController;
use suplascripts\controllers\thermostat\ThermostatProfilesController;
use suplascripts\controllers\thermostat\ThermostatRoomsController;
use suplascripts\controllers\thermostat\ThermostatsController;
use suplascripts\controllers\TokensController;
use suplascripts\controllers\UsersController;
use suplascripts\controllers\voice\VoiceCommandsController;

require __DIR__ . '/vendor/autoload.php';
ini_set('display_errors', 'Off');
ini_set("log_errors", 1);
ini_set("error_log", Application::VAR_PATH . "/logs/error.log");

$app = new Application();
$app->group('/api', function () use ($app) {
    $app->get('/time', SystemController::class . ':getTime');
    $app->get('/info', SystemController::class . ':getInfo');
    $app->group('/tokens', function () use ($app) {
        $app->post('/new', TokensController::class . ':createToken');
        $app->post('/client', TokensController::class . ':createTokenForClient');
        $app->put('', TokensController::class . ':refreshToken');
    });
    $app->group('/users', function () use ($app) {
        $app->post('/register', UsersController::class . ':post');
        $app->get('/{id}', UsersController::class . ':get');
        $app->patch('/{id}', UsersController::class . ':patch');
        $app->put('/{id}', UsersController::class . ':put');
        $app->delete('/{id}', UsersController::class . ':delete');
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
        $app->get('', VoiceCommandsController::class . ':getList');
        $app->post('', VoiceCommandsController::class . ':post');
        $app->patch('', VoiceCommandsController::class . ':executeVoiceCommand');
        $app->put('/{id}', VoiceCommandsController::class . ':put');
        $app->delete('/{id}', VoiceCommandsController::class . ':delete');
        $app->get('/last', VoiceCommandsController::class . ':getLastVoiceCommand');
    });
});
$app->run();
