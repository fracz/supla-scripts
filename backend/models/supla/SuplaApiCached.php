<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\app\Application;
use suplascripts\models\User;

class SuplaApiCached extends SuplaApi {

    /** @var SuplaApi */
    private $api;
    /** @var User */
    private $user;

    static $states = [];

    public function __construct(User $user, SuplaApi $api) {
        $this->api = $api;
        $this->user = $user;
    }

    public function getDevices(): array {
        return $this->getFromCache(__METHOD__, [], function () {
            return $this->api->getDevices();
        });
    }

    public function getChannelWithState(int $channelId) {
        return $this->getFromCache(__METHOD__, [$channelId], function () use ($channelId) {
            return $this->api->getChannelWithState($channelId);
        }, $channelId);
    }

    public function getChannelState(int $channelId) {
        if (isset(self::$states[$channelId])) {
            Application::getInstance()->metrics->increment('cache_hit');
            return self::$states[$channelId];
        }
        return $this->getFromCache(__METHOD__, [$channelId], function () use ($channelId) {
            $state = $this->api->getChannelState($channelId);
            self::rememberState($state);
            return $state;
        }, $channelId);
    }

    public function turnOn(int $channelId) {
        $this->clearCache($channelId);
        return $this->api->turnOn($channelId);
    }

    public function turnOff(int $channelId) {
        $this->clearCache($channelId);
        return $this->api->turnOff($channelId);
    }

    public function toggle(int $channelId) {
        $this->clearCache($channelId);
        return $this->api->toggle($channelId);
    }

    public function setRgb(int $channelId, string $color, int $colorBrightness = 100, int $brightness = 100) {
        $this->clearCache($channelId);
        return $this->api->setRgb($channelId, $color, $colorBrightness, $brightness);
    }

    public function getSensorLogs(int $channelId, $fromTime = '-1day', $toTime = 'now'): array {
        $this->clearCache($channelId);
        return $this->api->getSensorLogs($channelId, $fromTime, $toTime);
    }

    public function shut(int $channelId, int $percent = 100) {
        $this->clearCache($channelId);
        return $this->api->shut($channelId, $percent);
    }

    public function reveal(int $channelId, int $percent = 100) {
        $this->clearCache($channelId);
        return $this->api->reveal($channelId, $percent);
    }

    public function getClient(): SuplaApiClient {
        return $this->api->getClient();
    }

    private function getFromCache($method, array $arguments, callable $factory, $channelId = null) {
        $group = $this->user->id . ($channelId ? '/' . $channelId : '');
        $key = \FileSystemCache::generateCacheKey([$method, $arguments], $group);
        $value = \FileSystemCache::retrieve($key);
        if ($value) {
            Application::getInstance()->metrics->increment('cache_hit');
        } else {
            Application::getInstance()->metrics->increment('cache_miss');
            $value = $factory();
            \FileSystemCache::store($key, $value, 60);
        }
        return $value;
    }

    public function clearCache($channelId = null) {
        $group = $this->user->id . ($channelId ? '/' . $channelId : '');
        @\FileSystemCache::invalidateGroup($group);
    }

    public static function rememberState(int $channelId, $state) {
        if (is_array($state)) {
            $state = json_decode(json_encode($state));
        }
        self::$states[$channelId] = $state;
    }
}
