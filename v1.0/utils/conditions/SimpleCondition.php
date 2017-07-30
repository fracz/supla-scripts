<?php
namespace SuplaScripts\utils\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

class SimpleCondition implements StatusCondition
{
    private $channelId;
    private $expectation;

    public function __construct($channelId, array $expectation)
    {
        $this->channelId = $channelId;
        $this->expectation = $expectation;
    }

    /** @return bool */
    public function isFulfilled(ConfiguredSuplaApiClient $client)
    {
        $channelData = $client->channel($this->channelId);
        foreach ($this->expectation as $expectedProp => $expectedValue) {
            $actualValue = $channelData->{$expectedProp};
            if ($actualValue != $expectedValue) {
                return false;
            }
        }
        return true;
    }
}
