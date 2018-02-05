<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;

class VoiceCommandsController extends BaseController {

    public function getLastVoiceCommandAction() {
        $this->ensureAuthenticated();
        $user = $this->getCurrentUser();
        return $this->response(['command' => $user->lastVoiceCommand]);
    }

    public function executeVoiceCommandAction() {
        $this->ensureAuthenticated();
        $request = $this->request()->getParsedBody();
        Assertion::notEmptyKey($request, 'command');
        $command = mb_strtolower($request['command'], 'UTF-8');
        $user = $this->getCurrentUser();
        $user->lastVoiceCommand = $command;
        $user->save();
        $matchedActions = 0;
        $feedbacks = [];
        $sceneExecutor = new SceneExecutor();
        $user->log('voice', 'Odebrano komendę głosową: ' . $command);
        foreach ($user->scenes as $scene) {
            if ($scene->voiceTriggers) {
                /** @var Scene $scene */
                foreach ($scene->voiceTriggers as $trigger) {
                    if (strpos($command, $trigger) !== false) {
                        if ($scene->lastUsed && $scene->lastUsed->getTimestamp() >= time() - 4) {
                            $scene->log('Zignorowano zbyt szybkie wykonanie komendy głosowej: ' . $command);
                            break;
                        }
                        ++$matchedActions;
                        $scene->log('Uruchomiono scenę na podstawie komendy: ' . $command);
                        $feedback = $sceneExecutor->executeWithFeedback($scene);
                        if ($feedback) {
                            $feedbacks[] = $feedback;
                        }
                        break;
                    }
                }
            }
        }
        return $this->response([
            'matchedActions' => $matchedActions,
            'feedback' => implode(PHP_EOL, $feedbacks),
        ]);
    }
}
