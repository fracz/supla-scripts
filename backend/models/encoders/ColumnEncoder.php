<?php

namespace suplascripts\models\encoders;

use suplascripts\models\Model;

interface ColumnEncoder {

    public function shouldEncode(Model $model, $key): bool;

    public function isEncoded($value): bool;

    public function encode($value);

    public function decode($value);
}
