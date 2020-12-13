<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Amp\Success;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\ClosureHandler;
use Maestro2\Core\Task\ClosureTask;
use Maestro2\Core\Task\ConditionalHandler;
use Maestro2\Core\Task\ConditionalTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;

class ConditionalHandlerTest extends HandlerTestCase
{
    private ReportManager $publisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->publisher = new ReportManager();
    }

    protected function createHandler(): Handler
    {
        return new ConditionalHandler(TestEnqueuer::fromHandlers([
            new ClosureHandler()
        ]), $this->publisher);
    }

    public function testExecutesTaskOnTruePredicate(): void
    {
        $context = $this->runTask(new ConditionalTask(
            predicate: function (Context $context): bool {
                return true;
            },
            task: new ClosureTask(fn (array $args, Context $context) => new Success($context->withVar('foo', 'bar')))
        ));

        self::assertEquals('bar', $context->var('foo'));
        self::assertEquals(0, $this->publisher->groups()->reports()->infos()->count(), 'Warning publshed');
    }

    public function testDoesNotExecuteTaskOnFalsePredicate(): void
    {
        $context = $this->runTask(new ConditionalTask(
            predicate: function (Context $context): bool {
                return false;
            },
            task: new ClosureTask(fn (array $args, Context $context) => new Success($context->withVar('foo', 'bar')))
        ));

        self::assertNull($context->var('foo'));
        self::assertEquals(1, $this->publisher->groups()->reports()->infos()->count(), 'Warning publshed');
    }
}
