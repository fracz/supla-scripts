<?php
namespace SuplaScripts\utils\valueproviders;

use SuplaScripts\ConfiguredSuplaApiClient;

class OnOffValueProvider extends ValueProvider
{
    private $valueWhenOn;
    private $valueWhenOff;

    public function __construct($channelId, $valueWhenOn = 'włączone', $valueWhenOff = 'wyłączone')
    {
        parent::__construct($channelId);
        $this->valueWhenOn = $valueWhenOn;
        $this->valueWhenOff = $valueWhenOff;
    }

    public function doGetVariables(ConfiguredSuplaApiClient $client)
    {
        $channelData = $client->channel($this->channelId);
        $variables = [];
        if (property_exists($channelData, 'on')) {
            $variables['on'] = $channelData->on ? $this->valueWhenOn : $this->valueWhenOff;
        }
        return $variables;
    }
}
