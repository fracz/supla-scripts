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

    public function getChannelWithState(int $channelId)
    {
        $response = $this->client->channel($channelId);
        $this->handleError($response);
        return $response;
    }

    public function turnOn(int $channelId) {
        $result = $this->client->channelTurnOn($channelId);
        if ($result === false) {
            $result = $this->toggleUnpredictable($channelId);
        }
        return $result !== false;
    }

    public function turnOff(int $channelId) {
        $result = $this->client->channelTurnOff($channelId);
        if ($result === false) {
            $result = $this->toggleUnpredictable($channelId);
        }
        return $result !== false;
    }

    private function toggleUnpredictable(int $channelId) {
        $result = $this->client->channelOpenClose($channelId);
        if ($result === false) {
            $result = $this->client->channelOpen($channelId);
        }
        return $result !== false;
    }

    private function handleError($response)
    {
        if (!$response) {
            throw new SuplaApiException($this->client);
        }
    }
}
