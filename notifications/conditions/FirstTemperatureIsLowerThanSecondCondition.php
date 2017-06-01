<?php
namespace SuplaScripts\notifications\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

class FirstTemperatureIsLowerThanSecondCondition implements NotificationCondition
{
    private $channelIdFirst;
    private $channelIdSecond;

    public function __construct($channelIdFirst, $channelIdSecond)
    {
        $this->channelIdFirst = $channelIdFirst;
        $this->channelIdSecond = $channelIdSecond;
    }

    /** @return bool */
    public function shouldShowNotification(ConfiguredSuplaApiClient $client)
    {
        $firstData = $client->channel($this->channelIdFirst);
        $secondData = $client->channel($this->channelIdSecond);
        return $firstData->temperature < $secondData->temperature;
    }
}
