<?php

namespace suplascripts\models\thermostat;

use Cron\CronExpression;

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

    public function getClosestStart() {
        return $this->getClosestHour($this->timeRange['timeStart'], 0);
    }

    public function getClosestEnd() {
        return $this->getClosestHour($this->timeRange['timeEnd'], 1439);
    }

    private function getClosestHour($timeSpec, $defaultTimeSpec) {
        $cronExpression = $this->getCronExpression($this->timeSpecToMinutesInDesiredTimezone($timeSpec, $defaultTimeSpec));
        $now = $this->getCurrentTimeInDesiredTimezone($timeSpec);
        return CronExpression::factory($cronExpression)->getNextRunDate($now);
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

    private function getCurrentTimeInDesiredTimezone($timeSpec): \DateTime {
        $now = new \DateTime();
        if (is_string($timeSpec)) {
            $datetime = new \DateTime($timeSpec);
            $now->setTimezone($datetime->getTimezone());
        }
        return $now;
    }

    private function timeSpecToMinutesInDesiredTimezone($timeSpec, $default): int
    {
        if (!is_string($timeSpec)) {
            return $default;
        }
        $datetime = new \DateTime($timeSpec);
        $time = explode(':', $datetime->format('H:i'));
        $minutes = $time[0] * 60 + $time[1];
        return max(0, min(1439, $minutes));
    }
}
