<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$config = require __DIR__ . '/config.php';

foreach ($config as $channelId => $expectations) {
    $info = $client->channel($channelId);
    foreach ($expectations as $prop => $expectedValue) {
        $actualValue = $info->{$prop};
        if ($actualValue != $expectedValue) {
            if (is_bool($expectedValue)) {
                $expectedValue = $expectedValue ? 'true' : 'false';
                $actualValue = $actualValue ? 'true' : 'false';
            }
            echo "Expectation failed! $channelId should have $prop set to $expectedValue but it has $actualValue!";
        }
    }
}
