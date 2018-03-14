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

    public function executeCommandFromString($command, User $user = null) {
        list($channelId, $action) = explode(self::CHANNEL_DELIMITER, $command);
        $args = explode(self::ARGUMENT_DELIMITER, $action);
        $action = array_shift($args);
        Assertion::inArray($action, ['turnOn', 'turnOff', 'toggle', 'getChannelState', 'setRgb', 'shut', 'reveal', 'setThermostatProfile']);
        if ($action == 'setThermostatProfile') {
            return (new ThermostatSceneExecutor())->setThermostatProfile($channelId, $args[0]);
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
        $scene->lastUsed = new \DateTime();
        $scene->save();
        $actions = is_array($scene->actions) ? array_filter($scene->actions) : [];
        if (count($actions)) {
            if ($actions[0]) {
                $this->executeCommandsFromString($scene->actions[0]);
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
        if ($scene->feedback) {
            $feedback = (new FeedbackInterpolator())->interpolate($scene->feedback);
            $scene->log('Odpowiedź: ' . $feedback);
            return $feedback;
        } else {
            return '';
        }
    }
}
