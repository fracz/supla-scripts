<?php
/**
 * https://siipo.la/blog/how-to-use-eloquent-orm-migrations-outside-laravel
 */
$settings = require_once __DIR__ . '/../settings.php';
$dbSettings = $settings['db'];
//$dbName = $dbSettings['driver'] == 'sqlite' ? __DIR__ . '/../../' . $dbSettings['database'] : $dbSettings['database'];
return [
    'paths' => [
        'migrations' => __DIR__ . '/migrations',
        'seeds' => __DIR__ . '/seeds',
    ],
    'migration_base_class' => \suplascripts\database\migrations\Migration::class,
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'db',
        'db' => [
            'adapter' => $dbSettings['driver'],
//            'host' => $dbSettings['host'],
            'name' => $dbSettings['database'],
//            'user' => $dbSettings['username'],
//            'pass' => $dbSettings['password']
        ]
    ]
];
