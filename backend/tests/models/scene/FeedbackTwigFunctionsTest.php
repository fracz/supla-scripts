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
    public function testRendersSunriseAndSunsetTemplates($template, $expected, $expectedPhp82) {
        $this->assertSame(
            PHP_VERSION_ID < 80300 ? $expectedPhp82 : $expected,
            (new FeedbackInterpolator())->interpolate($template)
        );
    }

    public function solarTemplates() {
        // Literal expectations, not computed through the implementation under test.
        // PHP 8.3 changed date_sun_info()'s solar altitude from -50/60 to -35/60 degrees.
        // Retain the existing PHP 8.3 (and historical PHP 7.4) values plus PHP 8.2's values.
        return [
            'Krakow summer sunrise' => ['{{ sunriseTime(50.06, 19.94, "2024-06-21") }}', '04:30', '04:28'],
            'Krakow summer sunset' => ['{{ sunsetTime(50.06, 19.94, "2024-06-21") }}', '20:53', '20:55'],
            'Krakow winter sunrise' => ['{{ sunriseTime(50.06, 19.94, "2024-12-21") }}', '07:36', '07:34'],
            'Krakow winter sunset' => ['{{ sunsetTime(50.06, 19.94, "2024-12-21") }}', '15:40', '15:42'],
            'timezone location sunrise' => ['{{ sunriseTime(null, null, "2024-06-21") }}', '04:14', '04:12'],
            'timezone location sunset' => ['{{ sunsetTime(null, null, "2024-06-21") }}', '21:01', '21:03'],
        ];
    }
}
