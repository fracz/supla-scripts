<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$latitude = $_GET['latitude'];
$longitude = $_GET['longitude'];

$client = new \SuplaScripts\ConfiguredSuplaApiClient();

foreach ($config as $place) {
    $distance = haversineGreatCircleDistance($latitude, $longitude, $place['latitude'], $place['longitude']);
    $method = 'channel' . ucfirst($place['actionOutside']);
    if ($distance <= $place['tolerance']) {
        $method = 'channel' . ucfirst($place['actionInside']);
    }
    $client->{$method}($place['channel']);
}

// http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}
