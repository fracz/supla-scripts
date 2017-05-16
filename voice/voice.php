<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('voice');

$command = $_GET['command'];
$command = base64_decode($command);
$command = mb_strtolower($command, 'UTF-8');

$client->log('Command: ' . $command);

$actions = 0;
$feedbacks = [];

foreach ($config as $cfg) {
    foreach ($cfg['commands'] as $cmd) {
        $cmd = mb_strtolower($cmd, 'UTF-8');
        if (strpos($command, $cmd) !== false) {
            $client->log("Executed command: $cmd ($cfg[action])");
            $client->executeCommandsFromString($cfg['action']);
            if ($cfg['feedback']) {
                $feedbacks[] = $cfg['feedback'];
            }
            ++$actions;
            break;
        }
    }
}

$client->log("Matched actions: $actions");

echo implode(PHP_EOL, $feedbacks);
