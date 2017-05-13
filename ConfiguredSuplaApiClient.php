<?php

namespace SuplaScripts;

use Supla\ApiClient\SuplaApiClient;

require __DIR__ . '/config.php';
require __DIR__ . '/http-basic-auth.php';

header('Content-Type: text/plain; charset=utf-8');

class ConfiguredSuplaApiClient extends SuplaApiClient
{
    public function __construct()
    {
        parent::__construct([
            'server' => SUPLA_SERVER,
            'clientId' => SUPLA_CLIENT_ID,
            'secret' => SUPLA_SECRET,
            'username' => SUPLA_USERNAME,
            'password' => SUPLA_PASSWORD,
        ]);
    }

    public function executeCommandFromString($command, $separator = ',')
    {
        $args = explode($separator, $command);
        $methodName = 'channel' . ucfirst(array_shift($command));
        call_user_func_array([$this, $methodName], $args);
    }

    public function executeCommandsFromString($commands, $separator = '|', $commandSeparator = ',')
    {
        $commands = explode($separator, $commands);
        foreach ($commands as $command) {
            $this->executeCommandFromString($command, $commandSeparator);
        }
    }
}
