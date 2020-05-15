<?php

namespace suplascripts\controllers;

class StateWebhookController extends BaseController {
    public function postAction() {
        $parsedBody = $this->request()->getParsedBody();
        $userId = $parsedBody['shortUserId'];

//        return $this->response($scene)->withStatus(201);
    }
}
