<?php

namespace SuplaScripts;

use Supla\ApiClient\SuplaApiClient;

require __DIR__ . '/config.php';
require __DIR__ . '/http-basic-auth.php';

define('LOGS_DIR', __DIR__ . '/logs');

header('Content-Type: text/plain; charset=utf-8');

class ConfiguredSuplaApiClient extends SuplaApiClient
{
    private $scriptName;
    private $alreadyLogged = false;

    public function __construct($scriptName = 'notset')
    {
        parent::__construct([
            'server' => SUPLA_SERVER,
            'clientId' => SUPLA_CLIENT_ID,
            'secret' => SUPLA_SECRET,
            'username' => SUPLA_USERNAME,
            'password' => SUPLA_PASSWORD,
        ], $auto_logout = true, $debug = false);
        $this->scriptName = $scriptName;
    }

    public function executeCommandFromString($command, $separator = ',')
    {
        $args = explode($separator, $command);
        $methodSuffix = ucfirst(array_shift($args));
        if ($methodSuffix == 'Get') {
            $methodSuffix = '';
        }
        $methodName = 'channel' . $methodSuffix;
        return call_user_func_array([$this, $methodName], $args);
    }

    public function executeCommandsFromString($commands, $separator = '|', $commandSeparator = ',')
    {
        $commands = explode($separator, $commands);
        $results = [];
        foreach ($commands as $command) {
            $results[] = $this->executeCommandFromString($command, $commandSeparator);
        }
        return $results;
    }

    public function readFromGetOrArgv($getName, $argvIndex = 1)
    {
        global $argv;
        if (isset($_GET[$getName]) && $_GET[$getName] !== '') {
            return $_GET[$getName];
        } else if (isset($argv[$argvIndex])) {
            return $argv[$argvIndex];
        } else {
            $error = "Could not read the $getName parameter.";
            $this->log($error);
            die($error);
        }
    }

    public function log($entry)
    {
        if (!$this->alreadyLogged) {
            $this->alreadyLogged = true;
            $entry = PHP_EOL . (new \DateTime())->format(\DateTime::ATOM) . PHP_EOL . "\t" . $entry;
        } else {
            $entry = "\t" . $entry;
        }
        $wrote = file_put_contents(LOGS_DIR . '/' . $this->scriptName . '.log', $entry . PHP_EOL, FILE_APPEND);
        if (!$wrote) {
            echo 'Can not write to log file.' . PHP_EOL;
        }
    }
}
