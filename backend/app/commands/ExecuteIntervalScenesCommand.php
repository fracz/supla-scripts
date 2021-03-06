<?php

namespace suplascripts\app\commands;

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
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        /** @var Scene[] $scenes */
        $scenes = Scene::where(Scene::NEXT_EXECUTION_TIME, '<=', $now)->orderBy(Scene::NEXT_EXECUTION_TIME)->limit(100)->get();
        //  $scenes = [Scene::find('85b47744-1b47-47bc-be59-320df0ae951a')];
        Application::getInstance()->metrics->count('interval_scenes', count($scenes));
        if ($input->isInteractive()) {
            $output->writeln('Number of scenes: ' . count($scenes));
        }
        $sceneExecutor = new SceneExecutor();
        foreach ($scenes as $scene) {
            Application::getInstance()->getContainer()['currentUser'] = $scene->user;
            $sceneExecutor->executeWithFeedback($scene);
            $scene->updateNextExecutionTime();
            $scene->save();
        }
        Application::getInstance()->metrics->send();
    }
}
