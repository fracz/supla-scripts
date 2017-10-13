<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\app\Application;
use suplascripts\models\User;

abstract class SuplaApi
{
    public static function getInstance(User $user): SuplaApi
    {
        $readOnly = Application::getInstance()->getSetting('readOnly', true);
        $cached = Application::getInstance()->getSetting('apiCache', true);
        $implementation = $readOnly
            ? new SuplaApiReadOnly($user)
            : new SuplaApiReal($user);
        if ($cached) {
            return new SuplaApiCached($user, $implementation);
        } else {
            return $implementation;
        }
    }

    public abstract function getDevices(): array;

    public abstract function getChannelWithState(int $channelId);

    public abstract function getChannelState(int $channelId);

    public abstract function turnOn(int $channelId);

    public abstract function turnOff(int $channelId);

    public abstract function toggle(int $channelId);

    public abstract function setRgb(int $channelId, string $color, int $colorBrightness = 100, int $brightness = 100);

    public abstract function getSensorLogs(int $channelId, $fromTime = '-1day'): array;

    public abstract function getClient(): SuplaApiClient;

    public abstract function shut(int $channelId, int $percent = 100);

    public abstract function reveal(int $channelId, int $percent = 100);

    public function clearCache($channelId = null)
    {
    }
}
