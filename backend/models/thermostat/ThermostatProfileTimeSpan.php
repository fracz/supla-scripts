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

    public function getClosestStart(\DateTime $from)
    {
        return $this->getClosestHour($from, $this->timeRange['timeStart'], '0:0');
    }

    public function getClosestEnd(\DateTime $from)
    {
        return $this->getClosestHour($from, $this->timeRange['timeEnd'], '23:59');
    }

    private function getClosestHour(\DateTime $from, $timeSpec, $defaultTimeSpec)
    {
        $cronExpression = $this->getCronExpression($timeSpec ?: $defaultTimeSpec);
        return CronExpression::factory($cronExpression)->getNextRunDate($from);
    }

    private function getCronExpression($timeSpec)
    {
        return "{$this->minutesPart($timeSpec)} {$this->hoursPart($timeSpec)} * * {$this->weekdaysPart()}";
    }

    private function weekdaysPart(): string
    {
        return count($this->weekdays) ? implode(',', $this->weekdays) : '*';
    }

    private function hoursPart(string $timeSpec): int
    {
        return intval(explode(':', $timeSpec)[0]);
    }

    private function minutesPart(string $timeSpec): int
    {
        return intval(explode(':', $timeSpec)[1]);
    }
}
