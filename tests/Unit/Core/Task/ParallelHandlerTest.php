<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Amp\Failure;
use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\ClosureHandler;
use Maestro2\Core\Task\ClosureTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\ParallelHandler;
use Maestro2\Core\Task\ParallelTask;
use RuntimeException;

class ParallelHandlerTest extends HandlerTestCase
{
    const GROUP = 'foo';

    private ReportManager $reportManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportManager = new ReportManager();
    }

    protected function defaultContext(): Context
    {
        return Context::withFacts(new GroupFact(self::GROUP));
    }

    protected function createHandler(): Handler
    {
        return new ParallelHandler(new TestEnqueuer(
            new HandlerFactory([
                new ClosureHandler()
            ]),
        ), $this->reportManager);
    }

    public function testRunsTasksInParallel(): void
    {
        self::assertEquals($this->defaultContext()->merge(Context::create([
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ])), $this->runTask(new ParallelTask([
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('one', 1)
                );
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('two', 2)
                );
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('three', 3)
                );
            }),
        ]), $this->defaultContext()));
    }

    public function testRunsTasksInParallelWhenTasksAreAssociative(): void
    {
        self::assertEquals($this->defaultContext()->merge(Context::create([
            'one' => 1,
            'two' => 2,
            'three' => 3,
        ])), $this->runTask(new ParallelTask([
            'foobar' => new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('one', 1)
                );
            }),
            'barfoo' => new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('two', 2)
                );
            }),
            'bazbar' => new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('three', 3)
                );
            }),
        ]), $this->defaultContext()));
    }

    public function testReportFailedTasks(): void
    {
        $context = $this->runTask(new ParallelTask([
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('one', 1)
                );
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Failure(new RuntimeException('Oh no!'));
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('three', 3)
                );
            }),
        ]));
        self::assertEquals($this->defaultContext()->merge(Context::create([
            'one' => 1,
            'three' => 3,
        ])), $context);
        self::assertCount(1, $this->reportManager->group(self::GROUP)->reports()->fails(), 'Published failure report');
    }
}
