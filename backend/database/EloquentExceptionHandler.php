<?php

namespace suplascripts\database;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;

class EloquentExceptionHandler implements ExceptionHandler
{
    public function report(Exception $e)
    {
        throw $e;
    }

    public function render($request, Exception $e)
    {
        throw $e;
    }

    public function renderForConsole($output, Exception $e)
    {
        throw $e;
    }
}
