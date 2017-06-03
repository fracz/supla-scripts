<?php
namespace SuplaScripts\utils\conditions;

class Conditions
{
    public static function isTurnedOn($channelId)
    {
        return new SimpleCondition($channelId, ['on' => true]);
    }

    public static function isTurnedOff($channelId)
    {
        return new SimpleCondition($channelId, ['on' => false]);
    }

    public static function isOpened($channelId)
    {
        return new SimpleCondition($channelId, ['hi' => false]);
    }

    public static function isClosed($channelId)
    {
        return new SimpleCondition($channelId, ['hi' => true]);
    }

    public static function anyOf()
    {
        return new AnyOfCondition(func_get_args());
    }

    public static function firstTemperatureIsLowerThanSecond($channelIdFirst, $channelIdSecond, $delta = 0.5)
    {
        return new FirstTemperatureIsLowerThanSecondCondition($channelIdFirst, $channelIdSecond, $delta);
    }
}
