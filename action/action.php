<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$channel = $_GET['channel'];
$action = $_GET['action'];

$methodName = 'channel' . ucfirst($action);
$client->{$methodName}($channel);

echo 'OK';
