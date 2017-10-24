<?php

namespace suplascripts\models\encoders;

use suplascripts\models\Model;

class ColumnJsonEncoder implements ColumnEncoder {

    public function shouldEncode(Model $model, $key): bool {
        return in_array($key, $model->getJsonEncoded());
    }

    public function isEncoded($value): bool {
        return $value && is_string($value);
    }

    public function encode($value) {
        return json_encode($value);
    }

    public function decode($value) {
        return json_decode($value, true);
    }
}
