<?php

require __DIR__ . '/../vendor/autoload.php';
$request = $_GET['commands'];

$client = new \SuplaScripts\ConfiguredSuplaApiClient();
$client->executeCommandsFromString($request);

echo 'OK';
