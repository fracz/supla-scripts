<?php

namespace suplascripts\models;

use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\models\supla\SuplaApi;

trait HasSuplaApi {
    use HasApp;

    protected function getApi(User $user = null): SuplaApi {
        $currentUser = $user ? $user : $this->getApp()->getCurrentUser();
        if (!$currentUser) {
            throw new Http403Exception();
        }
        return SuplaApi::getInstance($currentUser);
    }
}
