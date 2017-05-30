<?php
require __DIR__ . '/../vendor/autoload.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('notifications');
$config = require __DIR__ . '/config.php';

$query = $client->readFromGetOrArgv('query');

$client->log('Query: ' . $query);

if (isset($config[$query])) {
    $notificationConfig = $config[$query];
    $response = [
        'nextRunTimestamp' => calculateNextNotificationTime($notificationConfig),
        'awake' => isset($notificationConfig['awake']) ? $notificationConfig['awake'] : false,
    ];
    if (strtoupper($_SERVER['REQUEST_METHOD']) != 'PUT') {
        $client->log('Checking condition');
        $notificationData = $notificationConfig['condition']->getNotificationToSend($client);
        if ($notificationData) {
            $response['notification'] = array_merge($notificationConfig['notification'], [
                'actions' => array_map(function ($action) {
                    return array_intersect_key($action, ['label' => '', 'icon' => '', 'sound' => '', 'vibrate' => '', 'flash' => '']);
                }, $notificationConfig['actions']),
            ]);

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
        }
    }
    $client->log(json_encode($response));
    echo json_encode($response);
} else {
    echo count($config);
}

function calculateNextNotificationTime(array $notificationConfig)
{
    $time = isset($notificationConfig['time']) ? $notificationConfig['time'] : 60;
    if (is_int($time)) {
        return time() + $time;
    } else {
        if (!is_array($time)) {
            $time = [$time];
        }
        $nextRunDates = array_map(function ($cronExpression) {
            $cron = Cron\CronExpression::factory($cronExpression);
            return $cron->getNextRunDate()->getTimestamp();
        }, $time);
        return min($nextRunDates);
    }
}
