<?php

namespace suplascripts\controllers;

use Cron\CronExpression;
use suplascripts\models\notification\Notification;

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
        $response['nextRunTimestamp'] = $this->calculateNextNotificationTime($notification->getIntervals());
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

    function calculateNextNotificationTime($interval) {
        if (is_array($interval) && isset($interval['interval'])) {
            $interval = $interval['interval'];
        }
        if (is_int($interval)) {
            return time() + $interval;
        } else {
            if (!is_array($interval)) {
                $interval = [$interval];
            }
            $nextRunDates = array_map(function ($cronExpression) {
                $cron = CronExpression::factory($cronExpression);
                return $cron->getNextRunDate()->getTimestamp();
            }, $interval);
            return min($nextRunDates);
        }
    }
}
