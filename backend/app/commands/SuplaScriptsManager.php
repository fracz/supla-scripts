<?php

namespace suplascripts\app\commands;

use Symfony\Component\Console\Application;

class SuplaScriptsManager extends Application {

    public function __construct() {
        parent::__construct('supla-scripts', \suplascripts\app\Application::version());
        $this->addCommands([
            new BackupDbCommand(),
            new ClearCacheCommand(),
            new ClearDbLogsCommand(),
            new DispatchCyclicTasksCommand(),
            new DispatchThermostatCommand(),
            new DispatchTimeScenesCommand(),
            new DisplayLogoCommand(),
            new GenerateEncryptionKeyCommand(),
            new InitializeApplicationCommand(),
            new MetricsReleaseCommand(),
            new MigrateDbCommand(),
        ]);
    }
}
