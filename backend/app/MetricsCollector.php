<?php

namespace suplascripts\app;

use M6Web\Component\Statsd\Client;

class MetricsCollector extends Client
{
    /** @var bool */
    private $enabled;

    public function __construct(bool $enabled, array $servers)
    {
        parent::__construct($servers);
        $this->enabled = $enabled;
    }


    protected function addToSend($stats, $value, $sampleRate, $unit, $tags)
    {
        parent::addToSend('suplascripts.' . $stats, $value, $sampleRate, $unit, $tags);
    }

    public function send()
    {
        if ($this->enabled) {
            return parent::send();
        }
    }
}
