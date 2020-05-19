<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use suplascripts\models\User;

class StateWebhookController extends BaseController {
    use HasSuplaApi;

    public function postAction() {
        //$parsedBody = $this->request()->getParsedBody();
        $parsedBody = ['userShortUniqueId' => 'dc85740d-cb27-405b-9da3-e8be5c71ae5b', 'channelId' => 123,
            'state' => ['on' => true, 'connected' => true], 'timestamp' => time()];
        Assertion::keyExists($parsedBody, 'userShortUniqueId');
        /** @var User $user */
        $user = User::find([User::SHORT_UNIQUE_ID => $parsedBody['userShortUniqueId']])->firstOrFail();
        Assertion::keyExists($parsedBody, 'channelId');
        $channelId = $parsedBody['channelId'];
        Assertion::integer($channelId);
        $this->getApi()->clearCache($channelId);
        $this->triggerScenesExecution($user, $channelId);
    }

    private function triggerScenesExecution(User $user, int $channelId) {
        /** @var Scene[] $scenes */
        $scenes = $user->scenes()->getQuery()
            ->where(Scene::TRIGGER_CHANNELS, 'LIKE', '%' . $channelId . ',%')
            ->orWhere(Scene::TRIGGER_CHANNELS, 'LIKE', '%' . $channelId . ']%')
            ->get();
        $sceneExecutor = new SceneExecutor();
        foreach ($scenes as $scene) {
            $feedbackInterpolator = new FeedbackInterpolator($scene);
            $triggerState = boolval($feedbackInterpolator->interpolate($scene->trigger));
            if ($triggerState != $scene->lastTriggerState) {
                $scene->lastTriggerState = $triggerState;
                $scene->save();
                $sceneExecutor->executeWithFeedback($scene);
            }
        }
    }
}
