<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\app\Application;
use suplascripts\models\User;

abstract class SuplaApi {

    public static function getInstance(User $user): SuplaApi {
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

    abstract public function getDevices(): array;

    abstract public function getChannelWithState(int $channelId);

    abstract public function getChannelState(int $channelId);

    abstract public function turnOn(int $channelId);

    abstract public function turnOff(int $channelId);

    abstract public function toggle(int $channelId);

    abstract public function setRgb(int $channelId, string $color, int $colorBrightness = 100, int $brightness = 100);

    abstract public function getSensorLogs(int $channelId, $fromTime = '-1day'): array;

    abstract public function getClient(): SuplaApiClient;

    abstract public function shut(int $channelId, int $percent = 100);

    abstract public function reveal(int $channelId, int $percent = 100);

    public function clearCache($channelId = null) {
    }
}
