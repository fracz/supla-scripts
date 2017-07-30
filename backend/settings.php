<?php
if (is_readable(\suplascripts\app\Application::CONFIG_PATH)) {
    return json_decode(file_get_contents(\suplascripts\app\Application::CONFIG_PATH), true);
} else {
    error_log('Configuration file cannot be found! ' . \suplascripts\app\Application::CONFIG_PATH);
    echo json_encode(['configured' => false]);
    exit;
}
