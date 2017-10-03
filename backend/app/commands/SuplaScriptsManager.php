<?php

namespace suplascripts\app\commands;

use Symfony\Component\Console\Application;

class SuplaScriptsManager extends Application
{
    public function __construct()
    {
        parent::__construct('supla-scripts', \suplascripts\app\Application::version());
        $this->addCommands([
            new InitializeApplicationCommand(),
            new MigrateDbCommand(),
            new GenerateEncryptionKeyCommand(),
            new DispatchCyclicTasksCommand(),
            new DispatchThermostatCommand(),
            new ClearCacheCommand(),
        ]);
    }
}
