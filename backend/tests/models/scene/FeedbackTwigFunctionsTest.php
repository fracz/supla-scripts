<?php

namespace suplascripts\tests\models\scene;

use PHPUnit\Framework\TestCase;
use suplascripts\models\scene\FeedbackInterpolator;

class FeedbackTwigFunctionsTest extends TestCase {

    private $previousTimezone;

    protected function setUp(): void {
        $this->previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Warsaw');
    }

    protected function tearDown(): void {
        date_default_timezone_set($this->previousTimezone);
    }

    public function testRendersTimestampFromAnExplicitDate() {
        $interpolator = new FeedbackInterpolator();

        $this->assertSame('1705321800', $interpolator->interpolate('{{ timestamp("2024-01-15T12:30:00+00:00") }}'));
    }

    public function testTimestampHonorsExplicitOffsetsAndLocalTimezone() {
        $interpolator = new FeedbackInterpolator();

        $this->assertSame('1705321800', $interpolator->interpolate('{{ timestamp("2024-01-15 13:30:00") }}'));
        $this->assertSame('1705321800', $interpolator->interpolate('{{ timestamp("2024-01-15T13:30:00+01:00") }}'));
        $this->assertSame('1721046600', $interpolator->interpolate('{{ timestamp("2024-07-15 14:30:00") }}'));
    }

    public function testInvalidTimestampRendersAsEmptyFeedback() {
        $this->assertSame('', (new FeedbackInterpolator())->interpolate('{{ timestamp("not-a-date") }}'));
    }

    /** @dataProvider solarTemplates */
    public function testRendersSunriseAndSunsetTemplates($template, $expected) {
        $this->assertSame($expected, (new FeedbackInterpolator())->interpolate($template));
    }

    public function solarTemplates() {
        // Literal PHP 7.4 baseline values; retain the old solar calculation before migration.
        return [
            'Krakow summer sunrise' => ['{{ sunriseTime(50.06, 19.94, "2024-06-21") }}', '04:30'],
            'Krakow summer sunset' => ['{{ sunsetTime(50.06, 19.94, "2024-06-21") }}', '20:53'],
            'Krakow winter sunrise' => ['{{ sunriseTime(50.06, 19.94, "2024-12-21") }}', '07:36'],
            'Krakow winter sunset' => ['{{ sunsetTime(50.06, 19.94, "2024-12-21") }}', '15:40'],
            'timezone location sunrise' => ['{{ sunriseTime(null, null, "2024-06-21") }}', '04:14'],
            'timezone location sunset' => ['{{ sunsetTime(null, null, "2024-06-21") }}', '21:01'],
        ];
    }
}
