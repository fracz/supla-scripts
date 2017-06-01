<?php
namespace SuplaScripts\utils\valueproviders;

use SuplaScripts\ConfiguredSuplaApiClient;

abstract class ValueProvider
{
    protected $channelId;
    private $variables;

    public function __construct($channelId)
    {
        $this->channelId = $channelId;
    }

    /** @return array */
    public final function getVariables(ConfiguredSuplaApiClient $client)
    {
        if (is_array($this->variables)) {
            return $this->variables;
        } else {
            $variables = $this->doGetVariables($client);
            if (!is_array($variables)) {
                $variables = [];
                $client->log('Value provider ' . get_class($this) . ' returned no variables for channel ' . $this->channelId);
            }
            foreach (array_keys($variables) as $variableName) {
                $variableNameWithChannel = "$variableName|$this->channelId";
                $variables[$variableNameWithChannel] = $variables[$variableName];
            }
            return $this->variables = $variables;
        }
    }

    protected abstract function doGetVariables(ConfiguredSuplaApiClient $client);
}
