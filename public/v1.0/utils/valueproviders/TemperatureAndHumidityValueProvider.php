<?php
namespace SuplaScripts\utils\valueproviders;

use SuplaScripts\ConfiguredSuplaApiClient;

class TemperatureAndHumidityValueProvider extends ValueProvider
{
    private $precision;
    private $temperatureSuffix;
    /**
     * @var string
     */
    private $humiditySuffix;

    public function __construct($channelId, $precision = 2, $temperatureSuffix = '°C', $humiditySuffix = '%')
    {
        parent::__construct($channelId);
        $this->precision = $precision;
        $this->temperatureSuffix = $temperatureSuffix;
        $this->humiditySuffix = $humiditySuffix;
    }

    public function doGetVariables(ConfiguredSuplaApiClient $client)
    {
        $channelData = $client->channel($this->channelId);
        $variables = [];
        if (property_exists($channelData, 'temperature')) {
            $variables['temperature'] = number_format($channelData->temperature, $this->precision) . $this->temperatureSuffix;
        }
        if (property_exists($channelData, 'humidity')) {
            $variables['humidity'] = number_format($channelData->humidity, $this->precision) . $this->humiditySuffix;
        }
        return $variables;
    }
}
