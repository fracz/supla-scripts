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
            new DispatchTimeScenesExecutionCommand(),
            new DisplayLogoCommand(),
            new GenerateEncryptionKeyCommand(),
            new InitializeApplicationCommand(),
            new MetricsReleaseCommand(),
            new SendMetricsGaugesCommand(),
            new MigrateDbCommand(),
            new ClearRequestQuotaLogsCommand(),
            new OauthRefreshTokensCommand(),
            new ClearClientRegistrationCodesCommand(),
            new ClearInvalidUserTokensCommand(),
            new ExecuteSceneCommand(),
            new ExecuteIntervalScenesCommand(),
            new UserUpdateCommand(),
        ]);
    }
}
