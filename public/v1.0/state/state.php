<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$channel = $_GET['channel'];

$channelInfo = $client->channel($channel);

if (isset($channelInfo->on)) {
    echo $channelInfo->on ? 'ON' : 'OFF';
} else if (isset($channelInfo->hi)) {
    echo $channelInfo->hi ? 'CLOSED' : 'OPENED';
} else {
    echo 'DUNNO';
}
