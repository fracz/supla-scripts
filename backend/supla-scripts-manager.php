<?php

use suplascripts\app\commands\SuplaScriptsManager;

require __DIR__ . '/vendor/autoload.php';
$manager = new SuplaScriptsManager();
$manager->run();
