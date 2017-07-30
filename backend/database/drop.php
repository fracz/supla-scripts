<?php
require __DIR__ . '/../vendor/autoload.php';
$settings = require __DIR__ . '/../settings.php';
if (!$settings['displayErrorDetails']) {
    throw new LogicException("It appears you are not in the debug mode. If you want to drop the database, turn displaying of error details on.");
}
new \suplascripts\app\Application();
Illuminate\Database\Capsule\Manager::connection()->statement('DROP SCHEMA public CASCADE');

