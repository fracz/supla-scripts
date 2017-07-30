<?php

namespace suplascripts\controllers\exceptions;

class Http403Exception extends ApiException
{
    public function __construct($message = 'Forbidden.')
    {
        parent::__construct($message, 403);
    }
}
