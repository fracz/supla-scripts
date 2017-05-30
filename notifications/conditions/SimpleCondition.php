<?php
namespace SuplaScripts\notifications\expectations;


use SuplaScripts\ConfiguredSuplaApiClient;
use SuplaScripts\notifications\conditions\NotificationCondition;

class SimpleCondition implements NotificationCondition
{
    private $channelId;
    private $expectation;

    public function __construct($channelId, array $expectation)
    {
        $this->channelId = $channelId;
        $this->expectation = $expectation;
    }

    /** @return bool */
    public function getNotificationToSend(ConfiguredSuplaApiClient $client)
    {
        $channelData = $client->channel($this->channelId);
        foreach ($this->expectation as $expectedProp => $expectedValue) {
            $actualValue = $channelData->{$expectedProp};
            if ($actualValue != $expectedValue) {
                return true;
            }
        }
        return false;
    }
}
