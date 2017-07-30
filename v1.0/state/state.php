<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$channel = $_GET['channel'];

if ($client->channel($channel)->on) {
    echo 'ON';
} else {
    echo 'OFF';
}
