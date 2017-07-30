<?php

namespace suplascripts\models;

use suplascripts\app\Application;

trait HasApp
{
    protected function getApp(): Application
    {
        return Application::getInstance();
    }
}
