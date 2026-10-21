<?php

namespace suplascripts\tests\models\scene;

use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;
use suplascripts\models\log\StateLogEntry;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\User;
use suplascripts\tests\support\ApiTwigExtension;
use suplascripts\tests\support\TwigTestApplication;

class FeedbackTwigStateHistoryTest extends TestCase {

    private $previousResolver;
    private $capsule;

    protected function setUp(): void {
        parent::setUp();
        $this->previousResolver = \Illuminate\Database\Eloquent\Model::getConnectionResolver();
        $this->capsule = new Capsule();
        $this->capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->capsule->bootEloquent();
        $this->capsule->getConnection()->getSchemaBuilder()->create(StateLogEntry::TABLE_NAME, function ($table) {
            $table->string('id')->primary();
            $table->string(StateLogEntry::USER_ID);
            $table->integer(StateLogEntry::CHANNEL_ID);
            $table->text(StateLogEntry::STATE);
            $table->timestamp(StateLogEntry::CREATED_AT);
        });
    }

    protected function tearDown(): void {
        if ($this->previousResolver) {
            \Illuminate\Database\Eloquent\Model::setConnectionResolver($this->previousResolver);
        } else {
            \Illuminate\Database\Eloquent\Model::unsetConnectionResolver();
        }
        $this->capsule->getConnection()->disconnect();
        parent::tearDown();
    }

    public function testStateFunctionRendersConnectedAndDisconnectedStateProperties() {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->exactly(2))->method('getChannelState')->willReturnMap([
            [7, (object) ['connected' => true, 'temperature' => 21.5]],
            [8, (object) ['connected' => false, 'temperature' => 0]],
        ]);
        $template = '{% set connectedState = state(7) %}{% set disconnectedState = state(8) %}'
            . '{{ connectedState.connected ? connectedState.temperature : "offline" }}'
            . '|{{ disconnectedState.connected ? "online" : "offline" }}';

        $this->assertSame('21.5|offline', $this->render($template, $api));
    }

    public function testStateFunctionClearsCacheOnlyWhenNoCacheIsTrue() {
        $api = $this->createMock(SuplaApi::class);
        $api->expects($this->once())->method('clearCache')->with(7);
        $api->expects($this->exactly(3))->method('getChannelState')->willReturn((object) ['connected' => true]);
        $template = '{{ state(7).connected ? 1 : 0 }}';

        $this->assertSame('1', $this->render($template, $api, ['noCache' => true]));
        $this->assertSame('1', $this->render($template, $api));
        $this->assertSame('1', $this->render($template, $api, ['noCache' => false]));
    }

    public function testHistoryFunctionRendersOnlyMatchingRecentRowsAndDecodedState() {
        $this->insertLog('user-a', 7, '2024-01-01 00:00:00', ['temperature' => 10]);
        $this->insertLog('user-a', 7, '2024-01-02 00:00:00', ['temperature' => 20]);
        $this->insertLog('user-a', 7, '2024-01-03 00:00:00', ['temperature' => 30]);
        $this->insertLog('user-a', 8, '2024-01-02 00:00:00', ['temperature' => 80]);
        $this->insertLog('user-b', 7, '2024-01-02 00:00:00', ['temperature' => 90]);
        $api = $this->createMock(SuplaApi::class);

        $template = '{% for entry in history(7, 1704153600) %}{{ entry.state.temperature }}'
            . '{% if not loop.last %},{% endif %}{% else %}empty{% endfor %}';
        $rendered = $this->render($template, $api, [], 'user-a');

        $this->assertSame('20,10', $rendered);
    }

    public function testHistoryFunctionReturnsEmptyForNoMatchingRows() {
        $api = $this->createMock(SuplaApi::class);

        $template = '{% for entry in history(99, 1704153600) %}unexpected{% else %}empty{% endfor %}';
        $this->assertSame('empty', $this->render($template, $api, [], 'user-a'));
    }

    public function testHistoryFunctionLimitsResultsToTwentyRows() {
        for ($day = 1; $day <= 21; $day++) {
            $this->insertLog('user-a', 7, sprintf('2024-01-%02d 00:00:00', $day), ['day' => $day]);
        }
        $api = $this->createMock(SuplaApi::class);

        $template = '{% set entries = history(7, 1706745600) %}'
            . '{{ entries|length }}:{{ entries[0].state.day }}:{{ entries[19].state.day }}';
        $rendered = $this->render($template, $api);

        $this->assertSame('20:21:2', $rendered);
    }

    private function render(string $source, SuplaApi $api, array $context = [], string $userId = 'user-a'): string {
        $user = new User();
        $user->setRawAttributes(['id' => $userId]);
        $app = new TwigTestApplication($user);
        $twig = new \Twig_Environment(new \Twig_Loader_Array(['test' => $source]));
        $twig->addExtension(new ApiTwigExtension($api, $app));
        return $twig->createTemplate($source)->render($context);
    }

    private function insertLog(string $userId, int $channelId, string $createdAt, array $state): void {
        $this->capsule->getConnection()->table(StateLogEntry::TABLE_NAME)->insert([
            'id' => uniqid('', true),
            StateLogEntry::USER_ID => $userId,
            StateLogEntry::CHANNEL_ID => $channelId,
            StateLogEntry::STATE => json_encode($state),
            StateLogEntry::CREATED_AT => $createdAt,
        ]);
    }
}
