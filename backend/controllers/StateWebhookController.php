<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\HasSuplaApi;
use suplascripts\models\log\StateLogEntry;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use suplascripts\models\User;

class StateWebhookController extends BaseController {
    use HasSuplaApi;

    public function postAction() {
        $parsedBody = $this->request()->getParsedBody();
//        $parsedBody = ['userShortUniqueId' => 'dc85740d-cb27-405b-9da3-e8be5c71ae5b', 'channelId' => 123,
//            'state' => ['on' => true, 'connected' => true], 'timestamp' => time(),
//            'channelFunction' => 'LIGHTSWITCH', 'authToken' => 'XXX'];
        Assertion::keyExists($parsedBody, 'userShortUniqueId');
        /** @var User $user */
        $user = $this->ensureExists(User::where([User::SHORT_UNIQUE_ID => $parsedBody['userShortUniqueId']])->first());
        $this->getApp()->getContainer()['currentUser'] = $user;
        Assertion::keyExists($parsedBody, 'authToken');
        if (sha1($user->webhookToken) !== $parsedBody['authToken']) {
            throw new Http403Exception('Invalid authToken.');
        }
        Assertion::keyExists($parsedBody, 'channelId');
        $channelId = $parsedBody['channelId'];
        Assertion::integer($channelId);
        Assertion::keyExists($parsedBody, 'state');
        Assertion::isArray($parsedBody['state']);
        Assertion::keyExists($parsedBody, 'channelFunction');
        $this->getApi($user)->clearCache();//$channelId);
        if (in_array($parsedBody['channelFunction'], StateLogEntry::LOGGED_FUNCTIONS)) {
            $this->addStateLog($user, $channelId, $parsedBody['state'], $parsedBody['timestamp'] ?? time());
        }
        $this->triggerScenesExecution($user, $channelId);
    }

    private function addStateLog(User $user, int $channelId, array $state, int $timestamp) {
        $user->stateLogs()->create([
            StateLogEntry::CHANNEL_ID => $channelId,
            StateLogEntry::STATE => $state,
            StateLogEntry::CREATED_AT => Carbon::createFromTimestamp($timestamp)
        ]);
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
                $scene->log('Wykryto zmianę warunku wyzwolenia sceny - wykonuję.');
                $scene->lastTriggerState = $triggerState;
                $sceneExecutor->executeWithFeedback($scene);
                $scene->save();
            }
        }
    }

    public function putAction() {
        $parsedBody = $this->request()->getParsedBody();
//        $parsedBody = ['userShortUniqueId' => 'dc85740d-cb27-405b-9da3-e8be5c71ae5b', 'refreshToken' => 'XXX'];
        Assertion::keyExists($parsedBody, 'userShortUniqueId');
        /** @var User $user */
        $user = $this->ensureExists(User::where([User::SHORT_UNIQUE_ID => $parsedBody['userShortUniqueId']])->first());
        Assertion::keyExists($parsedBody, 'refreshToken');
        if ($user->webhookToken !== $parsedBody['refreshToken']) {
            throw new Http403Exception('Invalid refreshToken.');
        }
        $user->webhookToken = sha1(Uuid::getFactory()->uuid4());
        $user->save();
        return $this->response([
            'authToken' => sha1($user->webhookToken),
            'refreshToken' => $user->webhookToken,
            'expiresAt' => strtotime('+1 month'),
        ]);
    }
}
