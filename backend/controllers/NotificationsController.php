<?php

namespace suplascripts\controllers;

use suplascripts\models\notification\Notification;
use suplascripts\models\scene\FeedbackInterpolator;

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
        if ($notification->isConditionMet()) {
            $feedbackInterpolator = new FeedbackInterpolator();
            $response['show'] = [
                'header' => $feedbackInterpolator->interpolate($notification->header),
                'message' => $feedbackInterpolator->interpolate($notification->message),
            ];
            if ($automate) {
                $notification->log('Wyświetlono powiadomienie: ' . var_export($response['show'], true));
            }
        } else if ($automate) {
            $notification->log('Sprawdzanie stanu powiadomienia: nie wyświetlono');
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
}
