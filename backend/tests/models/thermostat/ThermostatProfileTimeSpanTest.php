<?php

namespace suplascripts\tests\models\thermostat;

use PHPUnit\Framework\TestCase;
use suplascripts\models\thermostat\ThermostatProfileTimeSpan;

class ThermostatProfileTimeSpanTest extends TestCase {

    /**
     * @dataProvider closestRunCases
     */
    public function testReturnsClosestStartOrEnd($method, array $profile, $from, $expected) {
        $timeSpan = new ThermostatProfileTimeSpan($profile);

        $this->assertSame($expected, $timeSpan->$method(new \DateTime($from))
            ->format('Y-m-d H:i:s T'));
    }

    public function closestRunCases() {
        return [
            'start later on the same day' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '10:30']],
                '2024-01-08 09:00:00 UTC', '2024-01-08 10:30:00 UTC',
            ],
            'start at the exact boundary means next occurrence' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '10:30']],
                '2024-01-08 10:30:00 UTC', '2024-01-09 10:30:00 UTC',
            ],
            'start rolls over to the next day' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '08:15']],
                '2024-01-08 20:00:00 UTC', '2024-01-09 08:15:00 UTC',
            ],
            'end later on the same day' => [
                'getClosestEnd', ['timeRange' => ['timeEnd' => '18:45']],
                '2024-01-08 17:00:00 UTC', '2024-01-08 18:45:00 UTC',
            ],
            'end at the exact boundary means next occurrence' => [
                'getClosestEnd', ['timeRange' => ['timeEnd' => '18:45']],
                '2024-01-08 18:45:00 UTC', '2024-01-09 18:45:00 UTC',
            ],
            'end rolls over to the next day' => [
                'getClosestEnd', ['timeRange' => ['timeEnd' => '18:45']],
                '2024-01-08 20:00:00 UTC', '2024-01-09 18:45:00 UTC',
            ],
            'selected weekday skips intervening days' => [
                'getClosestStart', ['weekdays' => [3], 'timeRange' => ['timeStart' => '09:00']],
                '2024-01-08 10:00:00 UTC', '2024-01-10 09:00:00 UTC',
            ],
            'weekend selection crosses from Friday to Sunday' => [
                'getClosestEnd', ['weekdays' => [0], 'timeRange' => ['timeEnd' => '12:00']],
                '2024-01-05 13:00:00 UTC', '2024-01-07 12:00:00 UTC',
            ],
            'empty weekdays means every day' => [
                'getClosestStart', ['weekdays' => [], 'timeRange' => ['timeStart' => '09:00']],
                '2024-01-08 10:00:00 UTC', '2024-01-09 09:00:00 UTC',
            ],
            'null start time falls back to midnight' => [
                'getClosestStart', ['timeRange' => ['timeStart' => null]],
                '2024-01-08 00:00:00 UTC', '2024-01-09 00:00:00 UTC',
            ],
            'empty start time falls back to midnight' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '']],
                '2024-01-08 00:00:00 UTC', '2024-01-09 00:00:00 UTC',
            ],
            'null end time falls back to 23:59' => [
                'getClosestEnd', ['timeRange' => ['timeEnd' => null]],
                '2024-01-08 23:00:00 UTC', '2024-01-08 23:59:00 UTC',
            ],
            'empty end time falls back to 23:59' => [
                'getClosestEnd', ['timeRange' => ['timeEnd' => '']],
                '2024-01-08 23:59:00 UTC', '2024-01-09 23:59:00 UTC',
            ],
            'input timezone is retained' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '09:00']],
                '2024-01-08 08:00:00 America/Los_Angeles', '2024-01-08 09:00:00 PST',
            ],
            'spring DST transition uses the local wall-clock schedule' => [
                'getClosestStart', ['timeRange' => ['timeStart' => '09:00']],
                '2024-03-09 10:00:00 America/New_York', '2024-03-10 09:00:00 EDT',
            ],
        ];
    }
}
