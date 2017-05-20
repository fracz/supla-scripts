<?php
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/config.php';

$client = new \SuplaScripts\ConfiguredSuplaApiClient('voice');

$command = file_get_contents('php://input');
$command = mb_strtolower($command, 'UTF-8');

$client->log('Command: ' . $command);

$actions = 0;
$feedbacks = [];

foreach ($config as $cfg) {
    foreach ($cfg['commands'] as $cmd) {
        $cmd = mb_strtolower($cmd, 'UTF-8');
        if (strpos($command, $cmd) !== false) {
            $client->log("Executed command: $cmd ($cfg[action])");
            $results = $client->executeCommandsFromString($cfg['action']);
            if (isset($cfg['feedback'])) {
                $feedback = preg_replace_callback('#{{\s*([a-z]+)\s*\|?\s*(\d+)?\s*}}#', function ($match) use ($results) {
                    $variable = $match[1];
                    $resultIndex = isset($match[2]) && $match[2] ? $match[2] : 0;
                    $value = $results[$resultIndex]->{$variable};
                    if ($variable == 'on') {
                        $value = $value ? 'włączone' : 'wyłączone';
                    } else if ($variable == 'hi') {
                        $value = $value ? 'zamknięta' : 'otwarta';
                    } else if (floatval($value)) {
                        $value = number_format($value, 1, ',', '');
                    }
                    return $value;
                }, $cfg['feedback']);
                $client->log("Feedback: " . $feedback);
                $feedbacks[] = $feedback;
            }
            ++$actions;
            break;
        }
    }
}

$client->log("Matched actions: $actions");

echo implode(PHP_EOL, $feedbacks);
