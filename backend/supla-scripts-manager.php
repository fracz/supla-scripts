<?php

use suplascripts\app\Application;
use suplascripts\app\commands\SuplaScriptsManager;

require __DIR__ . '/vendor/autoload.php';

new Application();

$manager = new SuplaScriptsManager();
$manager->run();
