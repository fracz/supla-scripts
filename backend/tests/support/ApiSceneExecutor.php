<?php

namespace suplascripts\tests\support;

use suplascripts\models\scene\SceneExecutor;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\User;

class ApiSceneExecutor extends SceneExecutor {

    private $api;

    public function __construct(SuplaApi $api) {
        $this->api = $api;
    }

    protected function getApi(User $user = null): SuplaApi {
        return $this->api;
    }
}
