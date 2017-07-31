<?php
if (is_readable(\suplascripts\app\Application::CONFIG_PATH)) {
    $settings = json_decode(file_get_contents(\suplascripts\app\Application::CONFIG_PATH), true);
    if ($settings['db']['driver'] == 'sqlite') {
        $settings['db']['database'] = __DIR__ . '/../' . $settings['db']['database'];
    }
    return $settings;
} else {
    error_log('Configuration file cannot be found! ' . \suplascripts\app\Application::CONFIG_PATH);
    echo json_encode(['configured' => false]);
    exit;
}
