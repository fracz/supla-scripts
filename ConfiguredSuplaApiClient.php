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
}
