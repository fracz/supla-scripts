<?php

namespace suplascripts\models\observers;

class ModelValidator
{
    public function saving($model)
    {
        $model->validate();
    }
}
