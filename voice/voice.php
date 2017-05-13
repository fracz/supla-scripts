<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$command = $_GET['command'];

$command = mb_strtolower($command, 'UTF-8');

foreach ($config as $cfg) {
    foreach ($cfg['commands'] as $cmd) {
        $cmd = mb_strtolower($cmd, 'UTF-8');
        if ($cmd == $command) {
            $actions = explode(',', $cfg['action']);
            foreach ($actions as $action) {
                $parts = explode('-', $action);
                $methodName = 'channel' . ucfirst($parts[1]);
                $client->{$methodName}($parts[0]);
            }
            break;
        }
    }
}

echo 'OK';
