<?php

namespace suplascripts\app\commands;

use Assert\Assertion;
use suplascripts\app\Application;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteIntervalScenesCommand extends Command {
    protected function configure() {
        $this
            ->setName('scene:dispatch-interval-scenes')
            ->setDescription('Executes scenes in intervals.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        Assertion::true(set_time_limit(60), 'Could not set the script execution time limit.');
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        /** @var Scene[] $scenes */
        $scenes = Scene::where(Scene::NEXT_EXECUTION_TIME, '<=', $now)
            ->where(Scene::ENABLED, true)
            ->orderBy(Scene::NEXT_EXECUTION_TIME)
            ->limit(80)
            ->get();
        //  $scenes = [Scene::find('85b47744-1b47-47bc-be59-320df0ae951a')];
        Application::getInstance()->metrics->count('interval_scenes', count($scenes));
        if ($input->isInteractive()) {
            $output->writeln('Number of scenes: ' . count($scenes));
        }
        foreach ($scenes as $scene) {
            if ($output->isVerbose()) {
                $output->write('Scene ' . $scene->id . '... ');
            }
            try {
                $this->executeIntervalScene($scene);
                if ($output->isVerbose()) {
                    $output->writeln('OK');
                }
            } catch (\Throwable $e) {
                if ($output->isVerbose()) {
                    $output->writeln('ERROR ON SAVE - disabling.');
                }
                $scene->enabled = false;
                $scene->save();
            }
        }
        Application::getInstance()->metrics->send();
    }

    private function executeIntervalScene(Scene $scene) {
        $sceneExecutor = new SceneExecutor();
        Application::getInstance()->getContainer()['currentUser'] = $scene->user;
        try {
            $sceneExecutor->executeWithFeedback($scene);
        } catch (\Throwable $e) {
            // ignore
        }
        $scene->updateNextExecutionTime();
        $scene->save();
    }
}
