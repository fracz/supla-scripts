<?php

namespace suplascripts\controllers;

class EventFeedController extends BaseController {
    public function receiveEventAction() {
        return $this->response($this->request()->getParsedBody());
    }
}
