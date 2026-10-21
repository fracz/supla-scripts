<?php

namespace suplascripts\tests\support;

use suplascripts\app\Application;
use suplascripts\models\User;

class TwigTestApplication extends Application {

    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function getCurrentUser() {
        return $this->user;
    }
}
