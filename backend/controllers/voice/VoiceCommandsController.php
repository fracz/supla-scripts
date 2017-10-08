<?php

namespace suplascripts\controllers\thermostat;

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
}
