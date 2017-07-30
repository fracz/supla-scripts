<?php

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('temperature');
$channel = $client->readFromGetOrArgv('channel');
$client->log('#' . $channel);

$response = $client->channel($channel);

if ($response) {
    $results = [];
    if (property_exists($response, 'temperature')) {
        $results[] = number_format($response->temperature, 2) . '°C';
    }
    if (property_exists($response, 'humidity')) {
        $results[] = number_format($response->humidity, 1) . '%';
    }
    if (count($results)) {
        $result = implode(', ', $results);
    } else {
        $result = "Channel #$channel seems not to be a thermistor.";
    }

} else {
    $result = 'Could not contact the SUPLA API';
}

$client->log($result);
echo $result;

