<?php
require __DIR__ . '/../vendor/autoload.php';

$thermostatConfig = require __DIR__ . '/config.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$temperatures = [];

foreach ($thermostatConfig['thermometers'] as $channelId) {
    $temperatures[] = $client->channel($channelId)->temperature;
}

$avgTemperature = array_sum($temperatures) / count($temperatures);

echo "Average temperature: $avgTemperature" . PHP_EOL;

if ($avgTemperature < $thermostatConfig['heatFrom']) {
    $client->channelTurnOn($thermostatConfig['heater']);
    echo "Heater turned on";
} else if ($avgTemperature > $thermostatConfig['heatTo']) {
    $client->channelTurnOff($thermostatConfig['heater']);
    echo "Heater turned off";
} else {
    echo "No action needed";
}
