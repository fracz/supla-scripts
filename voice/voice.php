<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$command = $_GET['command'];
$command = base64_decode($command);
$command = mb_strtolower($command, 'UTF-8');

foreach ($config as $cfg) {
    foreach ($cfg['commands'] as $cmd) {
        $cmd = mb_strtolower($cmd, 'UTF-8');
        if (strpos($command, $cmd) !== false) {
            $client->executeCommandsFromString($cfg['action']);
            break;
        }
    }
}

echo 'OK';
