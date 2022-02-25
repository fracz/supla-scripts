<?php

namespace suplascripts\models\scene;

use suplascripts\app\Application;
use suplascripts\models\BelongsToUser;
use suplascripts\models\User;

class NotificationSender {
    /** @var User */
    private $user;
    /** @var FeedbackInterpolator */
    private $feedbackInterpolator;

    public function __construct($subject = null) {
        $this->feedbackInterpolator = new FeedbackInterpolator($subject);
        if ($subject instanceof BelongsToUser) {
            $this->user = $subject->user;
        } elseif ($subject instanceof User) {
            $this->user = $subject;
        } else {
            throw new \InvalidArgumentException('You must specify user to send the notifications to.');
        }
    }

    public function send(array $notification) {
        $pushoverCredentials = $this->user->getPushoverCredentials();
        if ($pushoverCredentials) {
            $title = ($notification['title'] ?? '') ? $this->feedbackInterpolator->interpolate($notification['title']) : '';
            $message = ($notification['message'] ?? '') ? $this->feedbackInterpolator->interpolate($notification['message']) : '';
            $pushover = new \Pushover();
            $pushover->setToken($pushoverCredentials['token']);
            $pushover->setUser($pushoverCredentials['user']);
            $pushover->setTitle($title);
            $pushover->setMessage($message);
            if ($notification['devices'] ?? null) {
                $devices = array_map('trim', explode(',', $notification['devices']));
                $pushover->setDevice(implode(',', $devices));
            }
            Application::getInstance()->metrics->increment('notification_send');
            return $pushover->send();
        } else {
            return false;
        }
    }
}
