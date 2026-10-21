<?php

require __DIR__ . '/../bootstrap.php';

use Defuse\Crypto\Key;
use suplascripts\app\Application;
use suplascripts\app\commands\GenerateEncryptionKeyCommand;

// This suite rebuilds its schema. Refuse host execution and arbitrary databases.
if (!is_file('/app/.database-test-container') || getenv('TEST_DB_RESET') !== 'supla_scripts_test') {
    throw new RuntimeException('Use the database-tests Docker target and TEST_DB_RESET=supla_scripts_test.');
}
if (getenv('TEST_DB_DATABASE') !== 'supla_scripts_test') {
    throw new RuntimeException('Only the explicitly configured supla_scripts_test database is allowed.');
}
if (is_file(Application::CONFIG_PATH) || is_file(GenerateEncryptionKeyCommand::KEY_PATH)) {
    throw new RuntimeException('Refusing to overwrite existing application configuration or encryption key.');
}
foreach (['TEST_DB_HOST', 'TEST_DB_USERNAME', 'TEST_DB_PASSWORD'] as $variable) {
    if (getenv($variable) === false || getenv($variable) === '') {
        throw new RuntimeException('Missing required environment variable: ' . $variable);
    }
}

$dbSettings = [
    'driver' => 'mysql',
    'host' => getenv('TEST_DB_HOST'),
    'port' => getenv('TEST_DB_PORT') ?: '3306',
    'database' => 'supla_scripts_test',
    'username' => getenv('TEST_DB_USERNAME'),
    'password' => getenv('TEST_DB_PASSWORD'),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'timezone' => '+00:00',
];
// Validate fields before inserting them into a PDO DSN.
if (!preg_match('/^[a-zA-Z0-9_.:-]+$/D', $dbSettings['host']) || !ctype_digit((string) $dbSettings['port'])) {
    throw new RuntimeException('Invalid database host or port.');
}
$pdo = new PDO(
    'mysql:host=' . $dbSettings['host'] . ';port=' . $dbSettings['port'] . ';dbname=supla_scripts_test;charset=utf8',
    $dbSettings['username'],
    $dbSettings['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
if ($pdo->query('SELECT DATABASE()')->fetchColumn() !== 'supla_scripts_test') {
    throw new RuntimeException('Unexpected database connection.');
}
if ((int) $pdo->query("SELECT GET_LOCK('supla_scripts_test.integration', 0)")->fetchColumn() !== 1) {
    throw new RuntimeException('Another integration suite is already using supla_scripts_test.');
}
// Hold the lock connection until process exit, including migration subprocesses.
$GLOBALS['testDatabaseLock'] = $pdo;
$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $pdo->exec('DROP TABLE `supla_scripts_test`.`' . str_replace('`', '``', $table) . '`');
}
$pdo->exec('SET FOREIGN_KEY_CHECKS=1');

// The normal migration config boots Application. Run it in a child process so
// its observers/singleton cannot leak into model tests. All files are container-only.
mkdir(dirname(Application::CONFIG_PATH), 0700, true);
mkdir(dirname(GenerateEncryptionKeyCommand::KEY_PATH), 0700, true);
file_put_contents(Application::CONFIG_PATH, json_encode(['db' => $dbSettings]));
chmod(Application::CONFIG_PATH, 0600);
GenerateEncryptionKeyCommand::storeEncryptionKey(Key::createNewRandomKey()->saveToAsciiSafeString());
try {
    passthru('php vendor/bin/phinx migrate --configuration tests/database/phinx-config.php --environment db', $status);
    if ($status !== 0) {
        throw new RuntimeException('Application migrations failed on the empty test database.');
    }
} finally {
    unlink(Application::CONFIG_PATH);
}

$GLOBALS['testDatabaseSettings'] = $dbSettings;
