<?php

namespace suplascripts\models\thermostat;

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
    }

    public function isCooling(): bool
    {
        return ($this->state['action'] ?? '') == 'cooling';
    }

    public function cool()
    {
        $this->state['action'] = 'cooling';
    }

    public function hasAction(): bool
    {
        return $this->isCooling() || $this->isHeating();
    }

    public function hasConfig(): bool
    {
        return $this->hasHeatingConfiguration() || $this->hasCoolingConfiguration();
    }

    public function forceAction($action, int $minutes)
    {
        $this->state['action'] = $action;
        $this->state['forcedAction'] = true;
        $this->state['forcedUntil'] = time() + $minutes * 60;
        unset($this->state['target']);
    }

    public function hasForcedAction()
    {
        $isForced = isset($this->state['forcedAction']) && time() < $this->state['forcedUntil'];
        if (!$isForced && isset($this->state['forcedAction'])) {
            $this->clearForcedAction();
        }
        return $isForced;
    }

    public function clearForcedAction()
    {
        unset($this->state['forcedAction']);
        unset($this->state['forcedUntil']);
        $this->turnOff();
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
        if (!$this->hasForcedAction()) {
            if ($this->isCooling()) {
                $this->state['target'] = $this->getCoolTo();
            } else if ($this->isHeating()) {
                $this->state['target'] = $this->getHeatTo();
            }
        }
        return $this->state;
    }

    public function updateState(Thermostat $thermostat, $roomId)
    {
        $thermostat->roomsState = array_merge($thermostat->roomsState, [$roomId => $this->getState()]);
    }
}
