<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';
define('LOG_FILE', __DIR__ . '/voice.log');

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$command = $_GET['command'];
$command = base64_decode($command);
$command = mb_strtolower($command, 'UTF-8');

file_put_contents(LOG_FILE, PHP_EOL . (new DateTime())->format(DateTime::ATOM) . ': Command: ' . $command, FILE_APPEND);

$actions = 0;

foreach ($config as $cfg) {
    foreach ($cfg['commands'] as $cmd) {
        $cmd = mb_strtolower($cmd, 'UTF-8');
        if (strpos($command, $cmd) !== false) {
            file_put_contents(LOG_FILE, PHP_EOL . "\tExecuted command: $cmd ($cfg[action])", FILE_APPEND);
            $client->executeCommandsFromString($cfg['action']);
            ++$actions;
            break;
        }
    }
}

file_put_contents(LOG_FILE, PHP_EOL . "\tMatched actions: $actions" . PHP_EOL, FILE_APPEND);

echo 'OK';
