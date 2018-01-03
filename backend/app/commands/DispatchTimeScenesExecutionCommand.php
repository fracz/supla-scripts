<?php

namespace suplascripts\app\commands;

use suplascripts\models\scene\PendingScene;
use suplascripts\models\scene\SceneExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchTimeScenesExecutionCommand extends Command {

    protected function configure() {
        $this
            ->setName('dispatch:time-scenes-execution')
            ->setDescription('Executes pending time scenes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $sceneExecutor = new SceneExecutor();
        $pendingScenesQuery = PendingScene::where(PendingScene::EXECUTE_AFTER, '<=', $now);
        if ($pendingScenesQuery->count()) {
            $pendingScenes = $pendingScenesQuery->get();
            $pendingScenesQuery->delete();
            foreach ($pendingScenes as $pendingScene) {
                /** @var PendingScene $pendingScene */
                $sceneExecutor->executeCommandsFromString($pendingScene->actions, $pendingScene->scene->user);
            }
        }
    }
}
