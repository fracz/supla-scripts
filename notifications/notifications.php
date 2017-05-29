<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('notifications');
$config = require __DIR__ . '/config.php';

$query = $client->readFromGetOrArgv('query');

if (isset($config[$query])) {
    $notificationConfig = $config[$query];
    if (strtolower($_SERVER['REQUEST_METHOD']) != 'PUT') {
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
        $action = file_get_contents('php://input');
        if (isset($notificationConfig['actions'][$action])) {
            if (isset($notificationConfig['actions'][$action]['command'])) {
                $client->executeCommandsFromString($notificationConfig['actions'][$action]['command']);
            }
        } else {
            echo "Invalid action: " . $action;
        }
    }
} else {
    echo count($config);
}
