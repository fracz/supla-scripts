<?php

namespace suplascripts\controllers\exceptions;

class ApiException extends \Exception {

    private $data = [];

    public function __construct($message, $code = 400, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function setData(array $data): ApiException {
        $this->data = $data;
        return $this;
    }

    final public function getData(): array {
        return $this->data;
    }
}
