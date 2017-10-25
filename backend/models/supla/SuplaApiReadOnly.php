<?php

namespace suplascripts\models\supla;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use suplascripts\app\Application;
use suplascripts\models\User;

class SuplaApiReadOnly extends SuplaApiReal {

    /** @var Logger */
    private $logger;

    public function __construct(User $user) {
        parent::__construct($user);
        $this->logger = new Logger('app_logger');
        $file_handler = new StreamHandler(Application::VAR_PATH . "/logs/api-read-only.log");
        $this->logger->pushHandler($file_handler);
    }

    public function turnOn(int $channelId) {
        $this->logger->info('READ-ONLY: Turn on: ' . $channelId);
        return true;
    }

    public function turnOff(int $channelId) {
        $this->logger->info('READ-ONLY: Turn off: ' . $channelId);
        return true;
    }

    public function setRgb(int $channelId, string $color, int $colorBrightness = 100, int $brightness = 100) {
        $this->logger->info('READ-ONLY: Set RGB: ' . $channelId);
        return true;
    }

    public function toggle(int $channelId) {
        $this->logger->info('READ-ONLY: Toggle: ' . $channelId);
        return true;
    }

    public function shut(int $channelId, int $percent = 100) {
        $this->logger->info('READ-ONLY: Shut: ' . $channelId);
        return true;
    }

    public function reveal(int $channelId, int $percent = 100) {
        $this->logger->info('READ-ONLY: Reveal: ' . $channelId);
        return true;
    }
}
