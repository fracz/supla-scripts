<?php
namespace SuplaScripts\notifications\triggers;

use SuplaScripts\ConfiguredSuplaApiClient;

class TemperatureNotification implements NotificationTrigger
{
    private $channelId;

    public function __construct($channelId)
    {
        $this->channelId = $channelId;
    }

    /** @return bool */
    public function getNotification(ConfiguredSuplaApiClient $client)
    {
        $channelData = $client->channel($this->channelId);
        if (property_exists($channelData, 'temperature')) {
            return ['temperature' => number_format($channelData->temperature, 2) . '°C'];
        } else {
            $client->log("Channel #$this->channelId seems not to be a thermistor.");
        }
    }
}
