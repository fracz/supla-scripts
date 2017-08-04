<?php
require __DIR__ . '/../vendor/autoload.php';

$color = $_GET['color'];
$color = hexdec($color);
$channel = $_GET['channel'];

$client = new \SuplaScripts\ConfiguredSuplaApiClient('rgb');
$client->channelSetRGB($channel, $color ? $color : 1, $color ? 100 : 0);
