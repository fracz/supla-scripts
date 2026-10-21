<?php

namespace suplascripts\tests\models\scene;

use PHPUnit\Framework\TestCase;
use suplascripts\models\scene\FeedbackInterpolator;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\User;
use suplascripts\tests\support\ApiFeedbackInterpolator;

class FeedbackInterpolatorTest extends TestCase {

    public function testRendersAndTrimsTwigFeedback() {
        $interpolator = new FeedbackInterpolator();

        $this->assertSame('Temperature=21.5', $interpolator->interpolate('  Temperature={{ 20 + 1.5 }}  '));
    }

    /** @dataProvider registeredFilters */
    public function testRendersRegisteredTwigFilters($template, $expected) {
        $interpolator = new FeedbackInterpolator();

        $this->assertSame($expected, $interpolator->interpolate($template));
    }

    public function registeredFilters() {
        return [
            'JSON data' => ['{{ (\'{"temperature":21.5}\'|jsonDecode).temperature }}', '21.5'],
            'English color name' => ['{{ "#ff0000"|colorName }}', 'red'],
            'Polish color name' => ['{{ "#0000ff"|colorNamePl }}', 'niebieski'],
        ];
    }

    public function testPassesCachePreferenceToTwigContext() {
        $interpolator = new FeedbackInterpolator();
        $template = '{{ noCache ? "fresh" : "cached" }}';

        $this->assertSame('cached', $interpolator->interpolate($template));
        $this->assertSame('fresh', $interpolator->interpolate($template, true));
    }

    public function testReportsTemplateErrorsWithoutThrowing() {
        $interpolator = new FeedbackInterpolator();

        $feedback = $interpolator->interpolate('{{ missingFunction() }}');

        $this->assertStringStartsWith('{{ missingFunction() }} (ERROR:', $feedback);
        $this->assertStringContainsString('Unknown "missingFunction" function', $feedback);
    }

    /** @dataProvider conditionFeedback */
    public function testInterpretsConditionFeedback($feedback, $expected) {
        $interpolator = new FeedbackInterpolator();

        $this->assertSame($expected, $interpolator->isConditionMet($feedback));
    }

    public function conditionFeedback() {
        return [
            'null condition' => [null, false],
            'empty condition' => ['', false],
            'whitespace only' => ['   ', false],
            'zero' => ['0', false],
            'nonzero' => ['1', true],
            'true Twig expression' => ['{{ 2 > 1 }}', true],
            'false Twig expression' => ['{{ 2 < 1 }}', false],
            'missing Twig variable' => ['{{ missingVariable }}', false],
            // Existing behavior: truthy text shorter than six characters is accepted.
            'literal false is truthy text, not a boolean' => ['false', true],
            'five characters are accepted' => ['12345', true],
            'six characters are rejected' => ['123456', false],
            'template error is rejected' => ['{{ missingFunction() }}', false],
        ];
    }

    /** @dataProvider userTimezoneDates */
    public function testUsesUserTimezoneAndRestoresGlobalTimezone($date, $expected) {
        $user = new User();
        $user->setRawAttributes(['timezone' => 'Europe/Warsaw']);
        $interpolator = new FeedbackInterpolator($user);

        $this->assertSame($expected, $interpolator->interpolate('{{ time("' . $date . '") }}'));
        $this->assertSame('UTC', date_default_timezone_get());
    }

    public function userTimezoneDates() {
        return [
            'winter' => ['2024-01-15T12:30:00+00:00', '13:30'],
            'summer' => ['2024-07-15T12:30:00+00:00', '14:30'],
        ];
    }

    public function testRestoresTimezoneAfterTemplateError() {
        $user = new User();
        $user->setRawAttributes(['timezone' => 'America/New_York']);
        $interpolator = new FeedbackInterpolator($user);

        $this->assertStringContainsString('(ERROR:', $interpolator->interpolate('{{ missingFunction() }}'));
        $this->assertSame('UTC', date_default_timezone_get());
    }

    public function testInterpolatesLegacyChannelTemperature() {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->once())->method('getChannelState')->with(7)->willReturn((object) [
            'connected' => true,
            'temperature' => 21.256,
        ]);
        $interpolator = new ApiFeedbackInterpolator($api);

        $this->assertSame('Temperature: 21.26', $interpolator->interpolate('Temperature: {{7|temperature|number:2}}'));
    }

    /** @dataProvider legacyChannelFeedback */
    public function testInterpolatesLegacyChannelFeedback(array $state, $template, $expected) {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->once())->method('getChannelState')->with(7)->willReturn((object) $state);
        $interpolator = new ApiFeedbackInterpolator($api);

        $this->assertSame($expected, $interpolator->interpolate($template));
    }

    public function legacyChannelFeedback() {
        return [
            'on uses default true feedback' => [
                ['connected' => true, 'on' => true], '{{7|on|bool}}', '1',
            ],
            'off uses default false feedback' => [
                ['connected' => true, 'on' => false], '{{7|on|bool}}', '0',
            ],
            'on uses custom feedback' => [
                ['connected' => true, 'on' => true], '{{7|on|bool:ON,OFF}}', 'ON',
            ],
            'off uses custom feedback' => [
                ['connected' => true, 'on' => false], '{{7|on|bool:ON,OFF}}', 'OFF',
            ],
            'missing measurement defaults to zero' => [
                ['connected' => true], '{{7|temperature|number}}', '0.0',
            ],
            'disconnected channel is reported explicitly' => [
                ['connected' => false, 'on' => true], '{{7|on|bool}}', 'DISCONNECTED',
            ],
            'greater than comparison' => [
                ['connected' => true, 'temperature' => 21.5], '{{7|temperature|compare:>,20,warm,cold}}', 'warm',
            ],
            'less than comparison' => [
                ['connected' => true, 'temperature' => 21.5], '{{7|temperature|compare:<,20,warm,cold}}', 'cold',
            ],
            'greater than or equal comparison' => [
                ['connected' => true, 'temperature' => 21.5], '{{7|temperature|compare:>=,21.5,yes,no}}', 'yes',
            ],
            'less than or equal comparison' => [
                ['connected' => true, 'temperature' => 21.5], '{{7|temperature|compare:<=,21.5,yes,no}}', 'yes',
            ],
            'equal comparison' => [
                ['connected' => true, 'temperature' => 21.5], '{{7|temperature|compare:==,21.5,yes,no}}', 'yes',
            ],
        ];
    }

    public function testComparesMeasurementsFromTwoChannels() {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->exactly(2))->method('getChannelState')->willReturnMap([
            [7, (object) ['connected' => true, 'temperature' => 21.5]],
            [8, (object) ['connected' => true, 'temperature' => 22.5]],
        ]);
        $interpolator = new ApiFeedbackInterpolator($api);

        $this->assertSame('colder', $interpolator->interpolate('{{7|temperature|compare:<,8#temperature,colder,warmer}}'));
    }

    public function testRejectsUnsupportedComparisonOperators() {
        $api = $this->createMock(SuplaApi::class);
        $api->method('getChannelState')->willReturn((object) ['connected' => true, 'temperature' => 21.5]);
        $interpolator = new ApiFeedbackInterpolator($api);

        $this->expectException(\Assert\InvalidArgumentException::class);
        $interpolator->interpolate('{{7|temperature|compare:!=,21.5,yes,no}}');
    }
}
