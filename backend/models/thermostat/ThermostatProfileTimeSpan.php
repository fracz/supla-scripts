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

    public function getStartCronExpression()
    {
        return $this->getCronExpression($this->timeSpecToMinutesInDefaultTimezone($this->timeRange['timeStart'] ?? 0, 0));
    }

    public function getEndCronExpression()
    {
        return $this->getCronExpression($this->timeSpecToMinutesInDefaultTimezone($this->timeRange['timeStart'] ?? 1439, 1439));
    }

    private function getCronExpression($timeInMinutes)
    {
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

    private function timeSpecToMinutesInDefaultTimezone($timeSpec, $default): int
    {
        if (!is_string($timeSpec)) {
            return $default;
        }
        $datetime = new \DateTime($timeSpec);
        $datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $time = explode(':', $datetime->format('H:i'));
        $minutes = $time[0] * 60 + $time[1];
        return max(0, min(1439, $minutes));
    }
}
