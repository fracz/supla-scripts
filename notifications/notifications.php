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
        $notificationData = $notificationConfig['trigger']->getNotification($client);
        if ($notificationData) {
            $response['notification'] = array_merge($notificationConfig['notification'], [
                'actions' => array_map(function ($action) {
                    return array_intersect_key($action, ['label' => '', 'icon' => '', 'sound' => '', 'vibrate' => '', 'flash' => '']);
                }, isset($notificationConfig['actions']) ? $notificationConfig['actions'] : []),
            ]);
            if (is_array($notificationData)) {
                $engine = new StringTemplate\Engine;
                $response['notification']['title'] = $engine->render($response['notification']['title'], $notificationData);
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
        }
    }
    $client->log(json_encode($response));
    echo json_encode($response);
} else {
    echo count($config);
}

function calculateNextNotificationTime(array $notificationConfig)
{
    $interval = isset($notificationConfig['interval']) ? $notificationConfig['interval'] : 60;
    if (is_int($interval)) {
        return time() + $interval;
    } else {
        if (!is_array($interval)) {
            $interval = [$interval];
        }
        $nextRunDates = array_map(function ($cronExpression) {
            $cron = Cron\CronExpression::factory($cronExpression);
            return $cron->getNextRunDate()->getTimestamp();
        }, $interval);
        return min($nextRunDates);
    }
}
