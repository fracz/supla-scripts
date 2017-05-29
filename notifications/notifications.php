<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('notifications');
$config = require __DIR__ . '/config.php';

$query = $client->readFromGetOrArgv('query');

$client->log('Query: ' . $query);

if (isset($config[$query])) {
    $notificationConfig = $config[$query];
    if (strtoupper($_SERVER['REQUEST_METHOD']) != 'PUT') {
        $client->log('Reading status');
        $channelData = $client->channel($notificationConfig['channel']);
        $notificationNeeded = false;
        foreach ($notificationConfig['expectation'] as $expectedProp => $expectedValue) {
            $actualValue = $channelData->{$expectedProp};
            if ($actualValue != $expectedValue) {
                $notification = array_merge($notificationConfig['notification'], [
                    'actions' => array_map(function ($action) {
                        return array_intersect_key($action, ['label' => '', 'icon' => '']);
                    }, $notificationConfig['actions']),
                ]);
                $client->log(json_encode($notification));
                echo json_encode($notification);
                exit;
            }
        }
    } else {
        $client->log('Executing command');
        $action = file_get_contents('php://input');
        if (isset($notificationConfig['actions'][$action])) {
            if (isset($notificationConfig['actions'][$action]['command'])) {
                $command = $notificationConfig['actions'][$action]['command'];
                $client->executeCommandsFromString($command);
                $client->log("Executed: " . $command);
            } else {
                $client->log("No command defined for " . $action);
            }
        } else {
            $client->log("Invalid action: " . $action);
            echo "Invalid action: " . $action;
        }
    }
} else {
    echo count($config);
}
