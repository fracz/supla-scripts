<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('state-notifier');
$config = require __DIR__ . '/config.php';

//$command = $client->readFromGetOrArgv('command');

if (strtolower($_SERVER['REQUEST_METHOD']) != 'PUT') {
    $notificationsToShow = [];
    for ($notificationConfigIndex = 0; $notificationConfigIndex < count($config); ++$notificationConfigIndex) {
        $notificationConfig = $config[$notificationConfigIndex];
        $channelData = $client->channel($notificationConfig['channel']);
        $notificationNeeded = false;
        foreach ($notificationConfig['expectation'] as $expectedProp => $expectedValue) {
            $actualValue = $channelData->{$expectedProp};
            if ($actualValue != $expectedValue) {
                $notificationNeeded = true;
                break;
            }
        }
        if ($notificationNeeded) {
            $notificationsToShow[] = array_merge($notificationConfig['notification'], [
                'index' => $notificationConfigIndex,
                'actions' => array_map(function ($action) {
                    return $action['label'];
                }, $notificationConfig['actions']),
            ]);
        }
    }
    echo json_encode($notificationsToShow);
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
