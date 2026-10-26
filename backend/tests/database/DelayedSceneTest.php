<?php

namespace suplascripts\tests\database;

use Illuminate\Database\Eloquent\Model;
use suplascripts\app\Application;
use suplascripts\app\commands\DispatchTimeScenesCommand;
use suplascripts\app\commands\DispatchTimeScenesExecutionCommand;
use suplascripts\app\commands\ExecuteIntervalScenesCommand;
use suplascripts\models\scene\PendingScene;
use suplascripts\models\scene\Scene;
use suplascripts\models\scene\SceneExecutor;
use Symfony\Component\Console\Tester\CommandTester;

class DelayedSceneTest extends DatabaseTestCase {

    private $previousCurrentUser;
    private $hadCurrentUser;

    protected function setUp(): void {
        $resolver = Model::getConnectionResolver();
        $dispatcher = Model::getEventDispatcher();
        if (file_exists(Application::CONFIG_PATH)) {
            throw new \RuntimeException('Refusing to overwrite application configuration.');
        }
        file_put_contents(Application::CONFIG_PATH, json_encode([
            'db' => $GLOBALS['testDatabaseSettings'],
            'jwt' => ['key' => bin2hex(random_bytes(32))],
            'metrics' => ['enabled' => false],
        ]));
        chmod(Application::CONFIG_PATH, 0600);
        try {
            Application::getInstance();
        } finally {
            unlink(Application::CONFIG_PATH);
            if ($resolver) {
                Model::setConnectionResolver($resolver);
            } else {
                Model::unsetConnectionResolver();
            }
            if ($dispatcher) {
                Model::setEventDispatcher($dispatcher);
            } else {
                Model::unsetEventDispatcher();
            }
        }
        $container = Application::getInstance()->getContainer();
        $this->hadCurrentUser = $container->has('currentUser');
        $this->previousCurrentUser = $this->hadCurrentUser ? $container['currentUser'] : null;
        parent::setUp();
    }

    protected function tearDown(): void {
        $container = Application::getInstance()->getContainer();
        if ($this->hadCurrentUser) {
            $container['currentUser'] = $this->previousCurrentUser;
        } else {
            unset($container['currentUser']);
        }
        parent::tearDown();
    }

    public function testDelayedActionExecutesAnotherSceneAfterQueueReload() {
        $user = $this->createUser();
        $target = $user->scenes()->create([
            Scene::LABEL => 'Delayed target',
            Scene::ACTIONS => [],
            Scene::FEEDBACK => 'Target executed',
        ]);
        $source = $user->scenes()->create([
            Scene::LABEL => 'Conditional source',
            Scene::CONDITION => '{{ true }}',
            Scene::ACTIONS => [30 => $target->id . ';sceneExecute'],
        ]);
        $source = $source->fresh();

        $before = time();
        $dispatcher = Model::getEventDispatcher();
        Model::setEventDispatcher(Application::getInstance()->db->getConnection()->getEventDispatcher());
        try {
            (new SceneExecutor())->executeWithFeedback($source);

            $pending = PendingScene::where(PendingScene::SCENE_ID, $source->id)->firstOrFail();
            $this->assertSame($target->id . ';sceneExecute', $pending->actions);
            $this->assertSame([(string) $source->id], $pending->sceneStack);
            $this->assertGreaterThanOrEqual($before + 30, $pending->executeAfter->getTimestamp());
            $this->assertNull($target->fresh()->lastUsed);

            $tester = new CommandTester(new DispatchTimeScenesExecutionCommand());
            $tester->execute([], ['interactive' => false]);
            $this->assertNull($target->fresh()->lastUsed, 'A future action must not execute early.');
            $this->assertSame(1, $source->pendingScenes()->count());

            // Advance the queue item instead of sleeping; the real worker reloads it.
            $pending->executeAfter = new \DateTime('-1 second');
            $pending->save();
            $tester->execute([], ['interactive' => false]);

            $this->assertNotNull($target->fresh()->lastUsed, 'The delayed second scene must execute.');
            $this->assertSame(0, $source->pendingScenes()->count());
            $this->assertSame('Odpowiedź: Target executed', $target->user->logs()
                ->where('entityId', $target->id)->firstOrFail()->data);
        } finally {
            if ($dispatcher) {
                Model::setEventDispatcher($dispatcher);
            } else {
                Model::unsetEventDispatcher();
            }
        }
    }

    public function testSchedulerLaunchesTheDelayedSceneWorker() {
        $settings = Application::getInstance()->settings;
        $oldPath = $settings->get('phpPath');
        $oldIntervals = $settings->get('intervals');
        $settings->set('phpPath', '/bin/echo');
        // A single immediate launch, with a stub instead of a real automation worker.
        $settings->set('intervals', ['timeScenes' => 60]);
        try {
            $tester = new CommandTester(new DispatchTimeScenesCommand());
            $tester->execute([], ['interactive' => false]);
            $this->assertStringContainsString('dispatch:time-scenes-execution', $tester->getDisplay());
        } finally {
            $settings->set('phpPath', $oldPath);
            $settings->set('intervals', $oldIntervals);
        }
    }

    /** @dataProvider invalidIntervals */
    public function testInvalidIntervalIsDisabledWithoutStoppingLaterScenes($interval) {
        $user = $this->createUser();
        $invalid = $user->scenes()->create([
            Scene::LABEL => 'Invalid schedule',
            Scene::ACTIONS => [],
            Scene::FEEDBACK => 'Invalid scene',
            Scene::ENABLED => true,
        ]);
        $valid = $user->scenes()->create([
            Scene::LABEL => 'Valid schedule',
            Scene::ACTIONS => [],
            Scene::FEEDBACK => 'Valid scene executed',
            Scene::ENABLED => true,
        ]);
        // Reproduce legacy persisted schedules without invoking the save hook.
        $this->db->getConnection()->table(Scene::TABLE_NAME)->where('id', $invalid->id)->update([
            Scene::INTERVALS => $interval,
            Scene::NEXT_EXECUTION_TIME => '2020-01-01 00:00:00',
        ]);
        $this->db->getConnection()->table(Scene::TABLE_NAME)->where('id', $valid->id)->update([
            Scene::INTERVALS => '*/5 * * * *',
            Scene::NEXT_EXECUTION_TIME => '2020-01-02 00:00:00',
        ]);

        $tester = new CommandTester(new ExecuteIntervalScenesCommand());
        $tester->execute([], ['interactive' => false]);

        $invalid = $invalid->fresh();
        $this->assertFalse((bool) $invalid->enabled);
        $this->assertNull($invalid->nextExecutionTime);
        $this->assertSame($interval, $invalid->intervals, 'Preserve the schedule so the user can correct it.');
        $valid = $valid->fresh();
        $this->assertTrue((bool) $valid->enabled);
        $this->assertNotNull($valid->lastUsed, 'The dispatcher must continue after disabling the invalid scene.');
        $this->assertGreaterThan(time(), $valid->nextExecutionTime->getTimestamp());
    }

    public function invalidIntervals() {
        return [
            'missing step offset' => ['/5 * * * *'],
            'wildcard range offset' => ['*-59/5 * * * *'],
        ];
    }
}
