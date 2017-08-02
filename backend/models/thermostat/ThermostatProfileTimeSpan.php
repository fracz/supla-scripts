<?php

namespace suplascripts\models\thermostat;

class ThermostatProfileTimeSpan
{
    /** @var array */
    private $weekdays;
    /** @var array */
    private $timeRange;

    public function __construct(array $timeSpan)
    {
        $this->weekdays = $timeSpan['weekdays'] ?? [];
        $this->timeRange = $timeSpan['timeRange'] ?? [];
    }

    public function getStartCronExpression() {
        return $this->getCronExpression($this->timeRange['timeStart'] ?? 0);
    }

    public function getEndCronExpression() {
        return $this->getCronExpression(min($this->timeRange['timeEnd'] ?? 1439, 1439));
    }

    private function getCronExpression($timeInMinutes) {
        return "{$this->minutesPart($timeInMinutes)} {$this->hoursPart($timeInMinutes)} * * {$this->weekdaysPart()}";
    }

    private function weekdaysPart(): string
    {
        return count($this->weekdays) ? implode(',', $this->weekdays) : '*';
    }

    private function hoursPart(int $minutes): int
    {
        return floor($minutes / 60);
    }

    private function minutesPart(int $minutes): int
    {
        return $minutes % 60;
    }
}
