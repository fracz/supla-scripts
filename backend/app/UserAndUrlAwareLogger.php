<?php

namespace suplascripts\app;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use suplascripts\models\HasApp;

class UserAndUrlAwareLogger implements LoggerInterface {

    use HasApp;

    /** @var Logger */
    private $logger;

    /** @var HandlerInterface[] */
    private $defaultHandlers;

    private $nextLogFilename;
    /** @var int */
    private $level;

    public function __construct($level = Logger::NOTICE) {
        $this->level = $level;
        $this->logger = new Logger('app_logger');
        $this->defaultHandlers = [$this->logFileHandler('app')];
    }

    private function logFileHandler(string $filename): HandlerInterface {
        return new StreamHandler(Application::VAR_PATH . "/logs/$filename.log", $this->level);
    }

    private function buildContext(array $context): array {
        if ($this->nextLogFilename) {
            $this->logger->setHandlers([$this->logFileHandler($this->nextLogFilename)]);
            $this->nextLogFilename = null;
        } else {
            $this->logger->setHandlers($this->defaultHandlers);
        }
        $app = $this->getApp();
        if ($app) {
            $currentUser = $app->getCurrentUser();
            return array_merge([
                'url' => (string)$app->request->getUri(),
                'username' => $currentUser ? $currentUser->username : 'not authenticated',
            ], $context);
        } else {
            return $context;
        }
    }

    private function toCustomFile(string $filename): LoggerInterface {
        $this->nextLogFilename = $filename;
        return $this;
    }

    public function toSuplaLog(): LoggerInterface {
        return $this->toCustomFile('supla');
    }

    public function toThermostatLog(): LoggerInterface {
        return $this->toCustomFile('thermostat');
    }

    public function emergency($message, array $context = []) {
        $this->logger->emerg($message, $this->buildContext($context));
    }

    public function alert($message, array $context = []) {
        $this->logger->alert($message, $this->buildContext($context));
    }

    public function critical($message, array $context = []) {
        $this->logger->crit($message, $this->buildContext($context));
    }

    public function error($message, array $context = []) {
        $this->logger->err($message, $this->buildContext($context));
    }

    public function warning($message, array $context = []) {
        $this->logger->warn($message, $this->buildContext($context));
    }

    public function notice($message, array $context = []) {
        $this->logger->notice($message, $this->buildContext($context));
    }

    public function info($message, array $context = []) {
        $this->logger->info($message, $this->buildContext($context));
    }

    public function debug($message, array $context = []) {
        $this->logger->debug($message, $this->buildContext($context));
    }

    public function log($level, $message, array $context = []) {
        $this->logger->log(strtoupper($level), $message, $this->buildContext($context));
    }
}
