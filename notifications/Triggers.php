<?php
namespace SuplaScripts\notifications;

use SuplaScripts\notifications\triggers\SimpleTrigger;

class Triggers
{
    public static function isTurnedOn($channelId)
    {
        return new SimpleTrigger($channelId, ['on' => true]);
    }

    public static function isTurnedOff($channelId)
    {
        return new SimpleTrigger($channelId, ['on' => false]);
    }

    public static function isOpened($channelId)
    {
        return new SimpleTrigger($channelId, ['hi' => true]);
    }

    public static function isClosed($channelId)
    {
        return new SimpleTrigger($channelId, ['hi' => false]);
    }
}
