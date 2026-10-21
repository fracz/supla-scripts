<?php

namespace suplascripts\tests\support;

use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\User;

// Replace only the device API boundary; retain the real interpolation pipeline.
class ApiFeedbackInterpolator extends FeedbackInterpolator {

    private $api;

    public function __construct(SuplaApi $api, User $user = null) {
        $this->api = $api;
        parent::__construct($user);
    }

    protected function getApi(User $user = null): SuplaApi {
        return $this->api;
    }
}
