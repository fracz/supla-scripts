<?php

namespace suplascripts\app\commands;

use suplascripts\app\Application;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteSceneCommand extends Command {
    protected function configure() {
        $this
            ->setName('scene:execute')
            ->addArgument('sceneId', InputArgument::REQUIRED)
            ->setDescription('Executes scene by id.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $sceneId = $input->getArgument('sceneId');
        $scene = Scene::find($sceneId);
        if ($scene) {
            $container = Application::getInstance()->getContainer();
            $container['currentUser'] = $scene->user;
            $feedback = (new SceneExecutor())->executeWithFeedback($scene);
            $scene->log('Scena wykonana z konsoli.');
            if ($feedback) {
                $output->writeln($feedback);
            } else {
                $output->writeln('<info>OK</info>');
            }
        } else {
            $output->writeln('<error>No such scene.</error>');
            return 1;
        }
    }
}
