<?php

namespace suplascripts\controllers\voice;

use suplascripts\controllers\BaseController;

class VoiceCommandsController extends BaseController
{
    public function postAction()
    {
        $this->ensureAuthenticated();
        $parsedBody = $this->request()->getParsedBody();
        $voiceCommand = $this->getCurrentUser()->voiceCommands()->create($parsedBody);
        $voiceCommand->save();
        $voiceCommand->log('Utworzono komendę głosową');
        return $this->response($voiceCommand)->withStatus(201);
    }

    public function getLastVoiceCommandAction()
    {
        $this->ensureAuthenticated();
        $user = $this->getCurrentUser();
        return $this->response(['command' => $user->lastVoiceCommand]);
    }
}
