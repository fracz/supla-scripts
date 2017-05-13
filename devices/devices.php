<?php

if (php_sapi_name() != "cli") {
    die('Devices list is available only in command line.');
}

require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

$devices = $client->ioDevices()->iodevices;

foreach ($devices as $device) {
    echo "#{$device->id} $device->name";
    if ($device->comment) {
        echo " ($device->comment)";
    }
    echo PHP_EOL;
    foreach ($device->channels as $channel) {
        echo "  #$channel->id {$channel->function->name}";
        if ($channel->caption) {
            echo " ($channel->caption)";
        }
        echo PHP_EOL;
    }
}
