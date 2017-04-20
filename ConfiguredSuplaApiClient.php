<?php

namespace SuplaScripts;

use Supla\ApiClient\SuplaApiClient;

require __DIR__ . '/config.php';

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
