<?php
namespace SuplaScripts\notifications\triggers;

use SuplaScripts\ConfiguredSuplaApiClient;

class SimpleTrigger implements NotificationTrigger
{
    private $channelId;
    private $expectation;

    public function __construct($channelId, array $expectation)
    {
        $this->channelId = $channelId;
        $this->expectation = $expectation;
    }

    /** @return bool */
    public function getNotification(ConfiguredSuplaApiClient $client)
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
