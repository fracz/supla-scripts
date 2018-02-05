<?php

namespace suplascripts\controllers;

use suplascripts\models\notification\Notification;

class NotificationsController extends BaseController {
    public function getNotificationsCountAction() {
        $this->ensureAuthenticated();
        return ['count' => $this->getCurrentUser()->notifications()->getQuery()->count()];
    }

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
        $notification = $this->ensureExists($this->getCurrentUser()->notifications()->getQuery()->find($params)->first());
        return $this->response($notification);
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
