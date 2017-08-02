<?php

namespace suplascripts\app;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use suplascripts\models\HasApp;

class UserAndUrlAwareLogger implements LoggerInterface
{
    use HasApp;

    /** @var Logger */
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger('app_logger');
        $file_handler = new StreamHandler(Application::VAR_PATH . "/logs/app.log");
        $this->logger->pushHandler($file_handler);
    }

    private function buildContext(array $context): array
    {
        $currentUser = $this->getApp()->getCurrentUser();
        return array_merge([
            'url' => (string)Application::getInstance()->request->getUri(),
            'username' => $currentUser ? $currentUser->username : null,
        ], $context);
    }

    public function emergency($message, array $context = [])
    {
        $this->logger->emerg($message, $this->buildContext($context));
    }

    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $this->buildContext($context));
    }

    public function critical($message, array $context = [])
    {
        $this->logger->crit($message, $this->buildContext($context));
    }

    public function error($message, array $context = [])
    {
        $this->logger->err($message, $this->buildContext($context));
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warn($message, $this->buildContext($context));
    }

    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $this->buildContext($context));
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $this->buildContext($context));
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $this->buildContext($context));
    }

    public function log($level, $message, array $context = [])
    {
        $this->logger->addRecord($level, $message, $this->buildContext($context));
    }
}
