<?php

namespace suplascripts\controllers\exceptions;

class Http404Exception extends ApiException
{
    public function __construct($message = 'Element not found.')
    {
        parent::__construct($message, 404);
    }
}
