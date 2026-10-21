<?php

namespace suplascripts\tests\models\scene;

use Assert\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Supla\ApiClient\SuplaApiClient;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\supla\SuplaApiException;
use suplascripts\models\User;
use suplascripts\tests\support\ApiSceneExecutor;

class SceneExecutorTest extends TestCase {

    public function testExecutesCommandsInOrderAndReturnsEachApiResult() {
        $api = $this->createMock(SuplaApi::class);
        $actions = [];
        $api->expects($this->exactly(3))->method('clearCache');
        $api->expects($this->once())->method('turnOn')->willReturnCallback(function ($channelId) use (&$actions) {
            $actions[] = ['turnOn', $channelId];
            return true;
        });
        $api->expects($this->once())->method('turnOff')->willReturnCallback(function ($channelId) use (&$actions) {
            $actions[] = ['turnOff', $channelId];
            return false;
        });
        $api->expects($this->once())->method('toggle')->willReturnCallback(function ($channelId) use (&$actions) {
            $actions[] = ['toggle', $channelId];
            return true;
        });

        $results = $this->executor($api)->executeCommandsFromString(
            '10;turnOn|20;turnOff|30;toggle',
            new User()
        );

        $this->assertSame([['turnOn', 10], ['turnOff', 20], ['toggle', 30]], $actions);
        $this->assertSame([true, false, true], $results);
    }

    /** @dataProvider permittedApiActions */
    public function testDispatchesEveryPermittedApiAction($command, $method, array $arguments, $result) {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->once())->method('clearCache')->with($arguments[0]);
        $api->expects($this->once())->method($method)->with(...$arguments)->willReturn($result);

        $this->assertSame([$result], $this->executor($api)->executeCommandsFromString($command, new User()));
    }

    public function permittedApiActions() {
        return [
            'turn on' => ['41;turnOn', 'turnOn', [41], true],
            'turn off' => ['42;turnOff', 'turnOff', [42], false],
            'toggle' => ['43;toggle', 'toggle', [43], true],
            'channel state' => ['44;getChannelState', 'getChannelState', [44], (object) ['connected' => true, 'on' => true]],
            'RGB with explicit levels' => ['45;setRgb,#112233,60,25', 'setRgb', [45, '#112233', 60, 25], true],
            'RGB with default levels' => ['45;setRgb,#112233', 'setRgb', [45, '#112233', 100, 100], true],
            'shut with percentage' => ['46;shut,35', 'shut', [46, 35], true],
            'shut default percentage' => ['47;shut', 'shut', [47, 100], true],
            'reveal with percentage' => ['48;reveal,70', 'reveal', [48, 70], true],
            'reveal default percentage' => ['49;reveal', 'reveal', [49, 100], true],
        ];
    }

    public function testApiFailureBecomesFalseAndLaterCommandsContinue() {
        $api = $this->createMock(SuplaApi::class);
        $client = $this->createMock(SuplaApiClient::class);
        $client->method('getLastError')->willReturn('offline');
        $api->expects($this->exactly(2))->method('clearCache');
        $api->expects($this->exactly(2))->method('turnOn')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new SuplaApiException($client)),
                true
            );

        $this->assertSame([false, true], $this->executor($api)->executeCommandsFromString(
            '50;turnOn|51;turnOn',
            new User()
        ));
    }

    public function testFalseApiResultIsPreserved() {
        $api = $this->createMock(SuplaApi::class);
        $api->method('turnOff')->willReturn(false);

        $this->assertSame([false], $this->executor($api)->executeCommandsFromString('52;turnOff', new User()));
    }

    /** @dataProvider invalidActions */
    public function testInvalidActionIsRejectedBeforeDeviceAccess($action) {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->never())->method('clearCache');

        $this->expectException(InvalidArgumentException::class);
        $this->executor($api)->executeCommandsFromString('53;' . $action, new User());
    }

    public function invalidActions() {
        return [
            'unknown action' => ['notAnAction'],
            'device enumeration is not a scene action' => ['getDevices'],
            'cache management is not a scene action' => ['clearCache'],
            'raw client access is not a scene action' => ['getClient'],
        ];
    }

    public function testProgrammerErrorsAreNotConvertedToFalse() {
        $api = $this->createMock(SuplaApi::class);
        $api->method('turnOn')->willThrowException(new \LogicException('programmer error'));

        $this->expectException(\LogicException::class);
        $this->executor($api)->executeCommandsFromString('54;turnOn', new User());
    }

    private function executor(SuplaApi $api): ApiSceneExecutor {
        return new ApiSceneExecutor($api);
    }
}
