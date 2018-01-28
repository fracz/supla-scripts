<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use suplascripts\models\User;

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
        $feedbacks = [];
        $sceneExecutor = new SceneExecutor();
        $user->log('voice', 'Odebrano komendę głosową: ' . $command);
        $scenesToExecute = $this->findScenesToExecute($user, $command);
        foreach ($scenesToExecute as $scene) {
            if ($scene->lastUsed && $scene->lastUsed->getTimestamp() >= time() - 4) {
                $scene->log('Zignorowano zbyt szybkie wykonanie komendy głosowej: ' . $command);
            } else {
                $scene->log('Uruchomiono scenę na podstawie komendy: ' . $command);
                $feedback = $sceneExecutor->executeWithFeedback($scene);
                if ($feedback) {
                    $feedbacks[] = $feedback;
                }
            }
        }
        return $this->response([
            'matchedActions' => count($scenesToExecute),
            'feedback' => implode(PHP_EOL, $feedbacks),
        ]);
    }

    /** @return Scene[] */
    private function findScenesToExecute(User $user, string $command): array {
        $scenesToExecute = [];
        foreach ($user->scenes as $scene) {
            if ($scene->voiceTriggers) {
                /** @var Scene $scene */
                foreach ($scene->voiceTriggers as $trigger) {
                    if (strpos($command, $trigger) !== false) {
                        $scenesToExecute[] = $scene;
                        continue 2;
                    }
                }
            }
            if (strpos($command, $scene->label) !== false) {
                $scenesToExecute[] = $scene;
            }
        }
        return array_unique($scenesToExecute);
    }
}
