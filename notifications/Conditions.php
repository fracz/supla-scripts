<?php
namespace SuplaScripts\notifications;

use SuplaScripts\notifications\conditions\SimpleCondition;

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
        return new SimpleCondition($channelId, ['hi' => true]);
    }

    public static function isClosed($channelId)
    {
        return new SimpleCondition($channelId, ['hi' => false]);
    }
}
