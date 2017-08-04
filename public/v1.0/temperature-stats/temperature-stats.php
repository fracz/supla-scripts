<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient();
$channel = $_GET['channel'];
$from = strtotime($_GET['since']);
$stat = $_GET['stat'];

$count = $client->temperatureLogItemCount($channel);
$offset = max(0, $count->count - $count->record_limit_per_request + 1);
$history = $client->temperatureLogGetItems($channel, $offset)->log;
$history = array_filter($history, function ($item) use ($from) {
    return $item->date_timestamp > $from;
});

usort($history, function ($itemA, $itemB) {
    return intval(1000 * (floatval($itemA->temperature) - floatval($itemB->temperature)));
});

if ($stat == 'min') {
    printItem($history[0]);
} else if ($stat == 'max') {
    printItem(end($history));
} else if ($stat == 'avg') {
    $temps = array_map(function ($item) {
        return floatval($item->temperature);
    }, $history);
    $avg = array_sum($temps) / count($temps);
    echo number_format($avg, 2) . '°C';
}

function printItem($item)
{
    echo number_format($item->temperature, 2) . '°C (' . date('H:i d.m', $item->date_timestamp) . ')';
}
