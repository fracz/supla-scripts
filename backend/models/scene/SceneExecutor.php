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

    public function executeCommandFromString($command, User $user) {
        list($channelId, $action) = explode(self::CHANNEL_DELIMITER, $command);
        $args = explode(self::ARGUMENT_DELIMITER, $action);
        $action = array_shift($args);
        Assertion::inArray(
            $action,
            ['turnOn', 'turnOff', 'toggle', 'getChannelState', 'setRgb', 'shut', 'reveal', 'thermostatSetProfile', 'sceneExecute']
        );
        if ($action == 'thermostatSetProfile') {
            return (new ThermostatSceneExecutor())->setThermostatProfile($channelId, $args[0]);
        } elseif ($action == 'sceneExecute') {
            $sceneId = $channelId;
            /** @var Scene $scene */
            $scene = Scene::find($sceneId);
            if ($scene) {
                Assertion::eq($scene->user->id, $user->id);
                if (!in_array($sceneId, $this->sceneStack)) {
                    $this->sceneStack[] = $sceneId;
                    return $this->executeWithFeedback($scene);
                } else {
                    $scene->log('Nie wykonano sceny - wykryto rekurencyjne wykonania.');
                }
            }
        } else {
            array_unshift($args, $channelId);
            $this->getApi($user)->clearCache($channelId);
            return call_user_func_array([$this->getApi($user), $action], $args);
        }
    }

    public function executeCommandsFromString($commands, User $user) {
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
        if ($scene->lastUsed && $scene->lastUsed->getTimestamp() >= time() - 3) {
            $scene->log('Zignorowano zbyt szybkie wykonanie sceny.');
            return '';
        }
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
        $this->sceneStack[] = $scene->id;
        $actions = is_array($scene->actions) ? array_filter($scene->actions) : [];
        if (count($actions)) {
            if (isset($actions[0])) {
                $results = $this->executeCommandsFromString($scene->actions[0], $scene->user);
                $feedbackFromNestedScenes = implode(PHP_EOL, array_filter($results, 'is_string'));
                unset($actions[0]);
            }
            if ($actions) {
                $now = time();
                foreach ($actions as $offset => $pendingAction) {
                    $scene->pendingScenes()->create([
                        PendingScene::ACTIONS => $pendingAction,
                        PendingScene::EXECUTE_AFTER => (new \DateTime())->setTimestamp($now + $offset),
                        PendingScene::SCENE_STACK => $this->sceneStack,
                    ]);
                }
            }
            $scene->log('Wykonanie');
        }

        $notifications = $scene->notifications;
        if (is_array($notifications) && count($notifications)) {
            $notifier = new NotificationSender($scene);
            foreach ($notifications as $notification) {
                $notifier->send($notification);
            }
        }

        $feedback = $feedbackFromNestedScenes ?? '';
        if ($scene->feedback) {
            $feedback .= $feedback . PHP_EOL . $feedbackInterpolator->interpolate($scene->feedback);
        }
        $feedback = trim($feedback);
        if ($feedback) {
            $scene->log('Odpowiedź: ' . $feedback);
            return $feedback;
        } else {
            return '';
        }
    }

    public function executePendingScene(PendingScene $pendingScene) {
        $scene = $pendingScene->scene;
        $this->sceneStack = $pendingScene->sceneStack;
        $scene->lastUsed = new \DateTime();
        $scene->save();
        $scene->log('Wykonanie opóźnionej akcji');
        $this->executeCommandsFromString($pendingScene->actions, $pendingScene->scene->user);
    }
}
