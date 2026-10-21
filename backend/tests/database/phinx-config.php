<?php

// Preserve the real migration paths/base/environment, including application
// initialization needed by data migrations. Only add configurable TCP port.
$config = require __DIR__ . '/../../database/phinx-config.php';
$config['environments']['db']['port'] = getenv('TEST_DB_PORT') ?: '3306';
return $config;
