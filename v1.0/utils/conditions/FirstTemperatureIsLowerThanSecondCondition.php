<?php
namespace SuplaScripts\utils\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

class FirstTemperatureIsLowerThanSecondCondition implements StatusCondition
{
    private $channelIdFirst;
    private $channelIdSecond;
    private $delta;

    public function __construct($channelIdFirst, $channelIdSecond, $delta = 0.5)
    {
        $this->channelIdFirst = $channelIdFirst;
        $this->channelIdSecond = $channelIdSecond;
        $this->delta = $delta;
    }

    /** @return bool */
    public function isFulfilled(ConfiguredSuplaApiClient $client)
    {
        $firstData = $client->channel($this->channelIdFirst);
        $secondData = $client->channel($this->channelIdSecond);
        return $firstData->temperature < $secondData->temperature - $this->delta;
    }
}
