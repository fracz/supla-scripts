<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$request = $_GET['commands'];

$commands = explode(',', $request);

foreach ($commands as $command) {
    $parts = explode('-', $command);
    $methodName = 'channel' . ucfirst($parts[1]);
    $client->{$methodName}($parts[0]);
}

echo 'OK';
