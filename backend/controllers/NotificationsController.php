<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\notification\Notification;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\scene\SceneExecutor;

class NotificationsController extends BaseController {
    public function postAction() {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        /** @var Notification $notification */
        $notification = $this->getCurrentUser()->notifications()->create($parsedBody);
        $notification->save();
        $notification->log('Utworzono powiadomienie');
        return $this->response($notification)->withStatus(201);
    }

    public function getListAction() {
        $this->ensureAuthenticated();
        $notifications = $this->getCurrentUser()->notifications()->getQuery()->orderBy(Notification::LABEL)->get();
        return $this->response($notifications);
    }

    public function getAction($params) {
        $this->ensureAuthenticated();
        /** @var Notification $notification */
        $notification = $this->ensureExists($this->getCurrentUser()->notifications()->getQuery()->find($params)->first());
        $response = $notification->toArray();
        $response['nextRunTimestamp'] = $notification->calculateNextNotificationTime();
        $automate = $this->request()->getParam('automate', false);
        if ($notification->isConditionMet() && !$this->request()->isMethod('PATCH')) {
            $feedbackInterpolator = new FeedbackInterpolator();
            $response['show'] = [
                'header' => $feedbackInterpolator->interpolate($notification->header),
                'message' => $feedbackInterpolator->interpolate($notification->message),
            ];
            $response['nextRunTimestamp'] = $notification->calculateNextNotificationTime(true);
            if ($automate) {
                $notification->log('Wyświetlono powiadomienie: ' . $response['show']['header'] . ' / ' . $response['show']['message']);
            }
        } elseif ($automate) {
            $notification->log('Sprawdzanie stanu powiadomienia: nie wyświetlono');
        }
        if ($this->request()->isMethod('PATCH')) {
            $response['nextRunTimestamp'] = $notification->calculateNextNotificationTime(true);
        }
        return $this->response($response);
    }

    public function putAction($params) {
        $this->ensureAuthenticated();
        /** @var Notification $notification */
        $notification = $this->ensureExists($this->getCurrentUser()->notifications()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        $notification->update($parsedBody);
        $notification->save();
        $notification->log('Wprowadzono zmiany w powiadomieniu.');
        return $this->response($notification);
    }

    public function deleteAction($params) {
        $this->ensureAuthenticated();
        $notifications = $this->ensureExists($this->getCurrentUser()->notifications()->getQuery()->find($params)->first());
        $notifications->log('Usunięto powiadomienie.');
        $notifications->delete();
        return $this->response()->withStatus(204);
    }

    public function executeActionAction($params) {
        $this->ensureAuthenticated();
        /** @var Notification $notification */
        $notification = $this->ensureExists($this->getCurrentUser()->notifications()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        Assertion::keyExists($parsedBody, 'action');
        $actionIndex = $parsedBody['action'];
        Assertion::inArray($actionIndex, [0, 1, 2]);
        Assertion::lessThan($actionIndex, count($notification->actions));
        $action = $notification->actions[$actionIndex];
        if (isset($action['scene']) && $action['scene']) {
            $sceneExecutor = new SceneExecutor();
            $sceneExecutor->executeCommandsFromString($action['scene']);
        }
        $notification->log('Wykonano akcję z powiadomienia: ' . $action['label']);
        return $this->getAction($params);
    }
}
