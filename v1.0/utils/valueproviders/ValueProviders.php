<?php
namespace SuplaScripts\utils\valueproviders;

class ValueProviders
{
    public static function temperatureAndHumidity($channelId, $precision = 2, $temperatureSuffix = '°C', $humiditySuffix = '%')
    {
        return new TemperatureAndHumidityValueProvider($channelId, $precision, $temperatureSuffix, $humiditySuffix);
    }

    public static function onOff($channelId, $valueWhenOn = 'włączone', $valueWhenOff = 'wyłączone')
    {
        return new OnOffValueProvider($channelId, $valueWhenOn, $valueWhenOff);
    }
}
