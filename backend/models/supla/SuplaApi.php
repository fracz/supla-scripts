<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\models\User;

class SuplaApi
{
    /** @var SuplaApiClient */
    private $client;


    public function __construct(User $user)
    {
        $apiCredentials = $user->getApiCredentials();
        $this->client = new SuplaApiClient($apiCredentials, false, false, false);
    }

    public function getDevices(): array
    {
        $response = $this->client->ioDevices();
        $this->handleError($response);
        return $response->iodevices;
    }

    private function handleError($response)
    {
        if (!$response) {
            throw new SuplaApiException($this->client);
        }
    }
}
