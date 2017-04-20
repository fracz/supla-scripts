<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();
$channel = $_GET['channel'];
$temp = $client->channel($channel)->temperature;
echo number_format($temp, 2) . '°C';
