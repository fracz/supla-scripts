<?php

namespace suplascripts\models\observers;

use suplascripts\app\Application;

class UserIdSetter {

    public function creating($model) {
        $user = Application::getInstance()->getCurrentUser();
        if ($user) {
            $model->userId = $user->id;
        }
    }
}
