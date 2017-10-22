<?php

use suplascripts\app\Application;
use suplascripts\app\commands\SuplaScriptsManager;

require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', 'On');
ini_set("log_errors", 1);
ini_set("error_log", Application::VAR_PATH . "/logs/error-manager.log");

new Application();

$manager = new SuplaScriptsManager();
$manager->run();
