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
use suplascripts\models\supla\ChannelFunction;
use suplascripts\models\supla\SuplaApiCached;
use suplascripts\models\User;

class StateWebhookController extends BaseController {
    use HasSuplaApi;

    public function postAction() {
        $parsedBody = $this->request()->getParsedBody();
//        $parsedBody = ['userShortUniqueId' => 'dc85740d-cb27-405b-9da3-e8be5c71ae5b', 'channelId' => 123,
//            'state' => ['on' => true, 'connected' => true], 'timestamp' => time(),
//            'channelFunction' => 'LIGHTSWITCH', 'authToken' => 'XXX'];
//        $parsedBody = ['userShortUniqueId' => 'dc85740d-cb27-405b-9da3-e8be5c71ae5b', 'channelId' => 123,
//            'triggered_actions' => ['HOLD'], 'timestamp' => time(),
//            'channelFunction' => 'ACTION_TRIGGER', 'authToken' => 'XXX'];
        Assertion::keyExists($parsedBody, 'userShortUniqueId');
        /** @var User $user */
        $user = $this->ensureExists(User::where([User::SHORT_UNIQUE_ID => $parsedBody['userShortUniqueId']])->first());
        $this->getApp()->getContainer()['currentUser'] = $user;
        $accessToken = $parsedBody['accessToken'] ?? null;
        if (!$accessToken) {
            $authHeader = $this->request()->getHeader('Authorization')[0] ?? '';
            $accessToken = substr($authHeader, strlen('Bearer '));
        }
        Assertion::true(!!$accessToken, 'Provide the access token in the accessToken field or the Authorization header.');
        if (sha1($user->webhookToken) !== $accessToken) {
            throw new Http403Exception('Invalid accessToken.');
        }
        Assertion::keyExists($parsedBody, 'channelId');
        $channelId = $parsedBody['channelId'];
        Assertion::integer($channelId);
        Assertion::keyExists($parsedBody, 'channelFunction');
        $channelFunction = $parsedBody['channelFunction'];
        if ($channelFunction === ChannelFunction::ACTION_TRIGGER()->getKey()) {
            Assertion::keyExists($parsedBody, 'triggered_actions');
            $triggeredActions = $parsedBody['triggered_actions'];
            Assertion::isArray($triggeredActions);
            SuplaApiCached::rememberState($channelId, $triggeredActions);
            $state = ['connected' => true, 'triggeredActions' => $triggeredActions];
            $this->addStateLog($user, $channelId, $state, $parsedBody['timestamp'] ?? time());
            $this->triggerScenesExecutionFromActionTrigger($user, $channelId, $triggeredActions);
        } else {
            Assertion::keyExists($parsedBody, 'state');
            $state = $parsedBody['state'] ?? [];
            Assertion::isArray($state);
            $this->getApi($user)->clearCache();//$channelId);
            SuplaApiCached::rememberState($channelId, $state);
            if (in_array($channelFunction, ChannelFunction::getFunctionNamesToStoreStateLogs())) {
                $this->addStateLog($user, $channelId, $state, $parsedBody['timestamp'] ?? time());
            }
            $this->triggerScenesExecution($user, $channelId);
        }
        return $this->response(['status' => 'ok'])->withStatus(202);
    }

    private function addStateLog(User $user, int $channelId, array $state, int $timestamp) {
        $user->stateLogs()->create([
            StateLogEntry::CHANNEL_ID => $channelId,
            StateLogEntry::STATE => $state,
            StateLogEntry::CREATED_AT => Carbon::createFromTimestamp($timestamp, new \DateTimeZone('UTC')),
        ]);
    }

    private function triggerScenesExecution(User $user, int $channelId) {
        /** @var Scene[] $scenes */
        $scenes = $user->scenes()->getQuery()
            ->where(Scene::ENABLED, true)
            ->where(function ($query) use ($channelId) {
                $query->where(Scene::TRIGGER_CHANNELS, 'LIKE', '%,' . $channelId . ',%')
                    ->orWhere(Scene::TRIGGER_CHANNELS, 'LIKE', '%,' . $channelId . ']%')
                    ->orWhere(Scene::TRIGGER_CHANNELS, 'LIKE', '%[' . $channelId . ']%')
                    ->orWhere(Scene::TRIGGER_CHANNELS, 'LIKE', '%[' . $channelId . ',%');
            })
            ->get();
        $sceneExecutor = new SceneExecutor();
        foreach ($scenes as $scene) {
            if ($scene->lastUsed && $scene->lastUsed->getTimestamp() >= time() - 3) {
                $scene->log('Zignorowano zbyt szybkie sprawdzenie wyzwalacza.');
                continue;
            }
            $feedbackInterpolator = new FeedbackInterpolator($scene);
            $feedback = $feedbackInterpolator->interpolate($scene->trigger);
            if (strpos($feedback, 'ERROR') !== false) {
                $scene->log('Błąd przy sprawdzeniu wyzwalacza sceny. ' . $feedback);
            } else {
                $triggerState = boolval($feedback);
                if ($triggerState != $scene->lastTriggerState) {
                    $scene->log('Wykryto zmianę warunku wyzwolenia sceny - wykonuję.');
                    $scene->lastTriggerState = $triggerState;
                    $sceneExecutor->executeWithFeedback($scene);
                    $scene->save();
                }
            }
        }
    }

    private function triggerScenesExecutionFromActionTrigger(User $user, int $channelId, array $triggeredActions) {
        /** @var Scene[] $scenes */
        $scenes = $user->scenes()->getQuery()
            ->where(Scene::ENABLED, true)
            ->where(function ($query) use ($channelId) {
                $query->where(Scene::ACTION_TRIGGERS, 'LIKE', '%:' . $channelId . ',%')
                    ->orWhere(Scene::ACTION_TRIGGERS, 'LIKE', '%:' . $channelId . ']%');
            })
            ->get();
        $sceneExecutor = new SceneExecutor();
        foreach ($scenes as $scene) {
            $triggered = array_filter($scene->actionTriggers, function ($actionTrigger) use ($channelId, $triggeredActions) {
                if ($actionTrigger['channelId'] == $channelId) {
                    return in_array($actionTrigger['trigger'], $triggeredActions);
                }
                return false;
            });
            if ($triggered) {
                $scene->log('Wykonuję scenę na podstawie wyzwalacza akcji.');
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
        $expiresAt = strtotime('+1 month');
        $expiresIn = $expiresAt - time();
        return $this->response([
            'accessToken' => sha1($user->webhookToken),
            'refreshToken' => $user->webhookToken,
            'expiresAt' => $expiresAt,
            'expiresIn' => $expiresIn,
        ]);
    }
}
