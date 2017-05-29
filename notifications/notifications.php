<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('state-notifier');
$config = require __DIR__ . '/config.php';

//$command = $client->readFromGetOrArgv('command');

if (strtolower($_SERVER['REQUEST_METHOD']) != 'PUT') {
    $query = $client->readFromGetOrArgv('query');
    if ($query == 'init') {
        echo count($config);
    } else if (isset($config[$query])) {
        $notificationConfig = $config[$query];
        $channelData = $client->channel($notificationConfig['channel']);
        $notificationNeeded = false;
        foreach ($notificationConfig['expectation'] as $expectedProp => $expectedValue) {
            $actualValue = $channelData->{$expectedProp};
            if ($actualValue != $expectedValue) {
                $notification = array_merge($notificationConfig['notification'], [
                    'actions' => array_map(function ($action) {
                        return $action['label'];
                    }, $notificationConfig['actions']),
                ]);
                echo json_encode($notification);
                exit;
            }
        }
    } else {
        echo "INVALID CONFIG OFFSET: " . $query;
    }
} else {

}

//foreach ($config as $channelId => $expectations) {
//    $info = $client->channel($channelId);
//    foreach ($expectations as $prop => $expectedValue) {
//        $actualValue = $info->{$prop};
//        if ($actualValue != $expectedValue) {
//            if (is_bool($expectedValue)) {
//                $expectedValue = $expectedValue ? 'true' : 'false';
//                $actualValue = $actualValue ? 'true' : 'false';
//            }
//            echo "Expectation failed! $channelId should have $prop set to $expectedValue but it has $actualValue!";
//        }
//    }
//}
