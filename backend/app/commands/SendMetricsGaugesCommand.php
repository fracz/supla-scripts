<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use suplascripts\models\Client;
use suplascripts\models\log\StateLogEntry;
use suplascripts\models\LogEntry;
use suplascripts\models\notification\Notification;
use suplascripts\models\scene\PendingScene;
use suplascripts\models\scene\Scene;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendMetricsGaugesCommand extends Command {

    protected function configure() {
        $this
            ->setName('metrics:gauges')
            ->setDescription('Sends all gauges to the metrics collector.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $metricsConfig = Application::getInstance()->getSetting('metrics', []);
        $metricsEnabled = $metricsConfig['enabled'] ?? false;
        if ($metricsEnabled) {
            Application::getInstance()->metrics->gauge('users', User::count());
            Application::getInstance()->metrics->gauge('users_oauth', User::whereNotNull(User::SHORT_UNIQUE_ID)->count());
            Application::getInstance()->metrics->gauge('users_webhook', User::whereNotNull(User::WEBHOOK_TOKEN)->count());
            Application::getInstance()->metrics->gauge('pending_scenes', PendingScene::count());
            Application::getInstance()->metrics->gauge('scenes', Scene::count());
            Application::getInstance()->metrics->gauge('notifications', Notification::count());
            Application::getInstance()->metrics->gauge('logs', LogEntry::count());
            Application::getInstance()->metrics->gauge('state_logs', StateLogEntry::count());
            Application::getInstance()->metrics->gauge('clients', Client::count());
            Application::getInstance()->metrics->gauge('thermostats_enabled', Thermostat::where(Thermostat::ENABLED, true)->count());
            Application::getInstance()->metrics->send();
        }
    }
}
