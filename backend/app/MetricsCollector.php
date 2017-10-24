<?php

namespace suplascripts\app;

use M6Web\Component\Statsd\Client;

class MetricsCollector extends Client {

    /** @var bool */
    private $enabled;
    /** @var string */
    private $instanceName;

    public function __construct(bool $enabled, string $instanceName, array $servers) {
        parent::__construct($servers);
        $this->enabled = $enabled;
        $this->instanceName = str_replace('.', '-', $instanceName);
    }


    protected function addToSend($stats, $value, $sampleRate, $unit, $tags) {
        parent::addToSend('suplascripts.' . $this->instanceName . '.' . $stats, $value, $sampleRate, $unit, $tags);
    }

    public function send() {
        if ($this->enabled) {
            return parent::send();
        }
    }
}
