<?php

namespace suplascripts\controllers\thermostat;

class ThermostatRoomConfig
{
    private $config;
    private $state;

    public function __construct(array $roomConfig, array $roomState)
    {
        $this->config = $roomConfig;
        $this->state = $roomState;
    }

    public function isHeating(): bool
    {
        return ($this->state['action'] ?? '') == 'heating';
    }

    public function heat()
    {
        $this->state['action'] = 'heating';
        $this->state['target'] = $this->getHeatTo();
    }

    public function isCooling(): bool
    {
        return ($this->state['action'] ?? '') == 'cooling';
    }

    public function cool()
    {
        $this->state['action'] = 'cooling';
        $this->state['target'] = $this->getCoolTo();
    }

    public function turnOff()
    {
        unset($this->state['action']);
        unset($this->state['target']);
    }

    public function hasHeatingConfiguration(): bool
    {
        return isset($this->config['heatFrom']) && isset($this->config['heatTo']);
    }

    public function hasCoolingConfiguration(): bool
    {
        return isset($this->config['coolFrom']) && isset($this->config['coolTo']);
    }

    public function shouldHeat(float $currentTemperature)
    {
        if ($this->hasHeatingConfiguration()) {
            if ($this->isHeating()) {
                return $currentTemperature <= $this->getHeatTo();
            } else {
                return $currentTemperature <= $this->config['heatFrom'];
            }
        }
        return false;
    }

    private function getHeatTo(): float
    {
        return $this->config['heatTo'];
    }

    public function shouldCool(float $currentTemperature)
    {
        if ($this->hasCoolingConfiguration()) {
            if ($this->isCooling()) {
                return $currentTemperature >= $this->getCoolTo();
            } else {
                return $currentTemperature >= $this->config['coolFrom'];
            }
        }
        return false;
    }

    private function getCoolTo(): float
    {
        return $this->config['coolTo'];
    }

    public function getState(): array
    {
        return $this->state;
    }
}
