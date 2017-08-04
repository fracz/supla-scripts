<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$channel = $_GET['channel'];

if ($client->channel($channel)->on) {
    $result = $client->channelTurnOff($channel);
    echo 'OFF';
} else {
    $result = $client->channelTurnOn($channel);
    echo 'ON';
}
