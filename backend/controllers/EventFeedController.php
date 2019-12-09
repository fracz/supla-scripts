<?php

namespace suplascripts\controllers;

use Assert\Assertion;
use suplascripts\models\AuditEntry;
use suplascripts\models\User;

class EventFeedController extends BaseController {
    public function receiveEventAction() {
        $newState = $this->request()->getParsedBody();
        Assertion::keyExists($newState, 'id');
        $channelId = $newState['id'];
        unset($newState['id']);
        unset($newState['caption']);
        /** @var User $user */
        $user = User::find('360225e1-133c-4bc0-9e6f-cba00d014bd3');
        $user->auditEntries()->create([
            AuditEntry::CHANNEL_ID => $channelId,
            AuditEntry::NEW_STATE => $newState,
        ]);
        return $this->response($newState);
    }
}
