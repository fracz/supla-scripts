<?php

namespace suplascripts\models\scene;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\supla\SuplaApiException;
use suplascripts\models\User;

class SceneExecutor {

    const OPERATION_DELIMITER = '|';
    const CHANNEL_DELIMITER = ';';
    const ARGUMENT_DELIMITER = ',';

    use HasSuplaApi;

    private $sceneStack = [];
    /** @var Scene */
    private $lastScene;

    public function executeCommandFromString($command, User $user = null) {
        list($channelId, $action) = explode(self::CHANNEL_DELIMITER, $command);
        $args = explode(self::ARGUMENT_DELIMITER, $action);
        $action = array_shift($args);
        Assertion::inArray($action, ['turnOn', 'turnOff', 'toggle', 'getChannelState', 'setRgb', 'shut', 'reveal', 'thermostatSetProfile', 'sceneExecute']);
        if ($action == 'thermostatSetProfile') {
            return (new ThermostatSceneExecutor())->setThermostatProfile($channelId, $args[0]);
        } else if ($action == 'sceneExecute') {
            $sceneId = $channelId;
            /** @var Scene $scene */
            $scene = Scene::find($sceneId);
            Assertion::eq($scene->user->id, $this->lastScene->user->id);
            if (!in_array($sceneId, $this->sceneStack)) {
                $this->sceneStack[] = $sceneId;
                return $this->executeWithFeedback($scene);
            } else {
                $scene->log('Nie wykonano sceny - wykryto rekurencyjne wykonania.');
            }
        } else {
            array_unshift($args, $channelId);
            $this->getApi($user)->clearCache($channelId);
            return call_user_func_array([$this->getApi($user), $action], $args);
        }
    }

    public function executeCommandsFromString($commands, User $user = null) {
        $commands = explode(self::OPERATION_DELIMITER, $commands);
        $results = [];
        foreach ($commands as $command) {
            try {
                $results[] = $this->executeCommandFromString($command, $user);
            } catch (SuplaApiException $e) {
                $results[] = false;
            }
        }
        return $results;
    }

    public function executeWithFeedback(Scene $scene): string {
        $this->lastScene = $scene;
        $scene->lastUsed = new \DateTime();
        $scene->save();
        $feedbackInterpolator = new FeedbackInterpolator($scene);
        if (is_string($scene->condition) && $scene->condition !== '') {
            $conditionMet = $feedbackInterpolator->interpolate($scene->condition, true);
            if (!$conditionMet) {
                $scene->log('Scena nie została wykonana - niespełniony warunek.');
                return '';
            }
        }
        $actions = is_array($scene->actions) ? array_filter($scene->actions) : [];
        if (count($actions)) {
            if ($actions[0]) {
                $results = $this->executeCommandsFromString($scene->actions[0]);
                unset($actions[0]);
            }
            if ($actions) {
                $now = time();
                foreach ($actions as $offset => $pendingAction) {
                    $scene->pendingScenes()->create([
                        PendingScene::ACTIONS => $pendingAction,
                        PendingScene::EXECUTE_AFTER => (new \DateTime())->setTimestamp($now + $offset),
                    ]);
                }
            }
            $scene->log('Wykonanie');
        }
        $feedbackFromNestedScenes = implode(PHP_EOL, array_filter($results, 'is_string'));
        $feedback = $feedbackFromNestedScenes;
        if ($scene->feedback) {
            $feedback .= $feedbackFromNestedScenes . PHP_EOL . $feedbackInterpolator->interpolate($scene->feedback);
        }
        if ($feedback) {
            $scene->log('Odpowiedź: ' . $feedback);
            return $feedback;
        } else {
            return '';
        }
    }
}
