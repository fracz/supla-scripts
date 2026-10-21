<?php

namespace suplascripts\tests\support;

use suplascripts\app\Application;
use suplascripts\models\scene\FeedbackTwigExtension;
use suplascripts\models\supla\SuplaApi;

class ApiTwigExtension extends FeedbackTwigExtension {

    private $api;
    private $app;

    public function __construct(SuplaApi $api, Application $app) {
        $this->api = $api;
        $this->app = $app;
    }

    protected function getApi(\suplascripts\models\User $user = null): SuplaApi {
        return $this->api;
    }

    protected function getApp(): Application {
        return $this->app;
    }
}
