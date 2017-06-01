<?php
namespace SuplaScripts\utils;

use StringTemplate\Engine;
use SuplaScripts\ConfiguredSuplaApiClient;
use SuplaScripts\utils\valueproviders\ValueProvider;

class MessageBuilder
{
    /**
     * @param ConfiguredSuplaApiClient $client
     * @param string $message
     * @param ValueProvider[] $valueProviders
     * @return string
     */
    public static function build(ConfiguredSuplaApiClient $client, $message, $valueProviders)
    {
        if (!is_array($valueProviders)) {
            $valueProviders = [$valueProviders];
        }
        $variables = [];
        foreach ($valueProviders as $valueProvider) {
            $variables = array_merge($variables, $valueProvider->getVariables($client));
        }
        $engine = new Engine();
        return $engine->render($message, $variables);
    }
}
