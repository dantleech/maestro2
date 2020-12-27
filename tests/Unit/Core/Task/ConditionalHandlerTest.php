<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\Success;
use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\ConditionalTask;
use Maestro\Core\Task\Context;

class ConditionalHandlerTest extends HandlerTestCase
{
    public function testExecutesTaskOnTruePredicate(): void
    {
        $context = $this->runTask(new ConditionalTask(
            predicate: function (Context $context): bool {
                return true;
            },
            task: new ClosureTask(fn (Context $context) => new Success($context->withVar('foo', 'bar')))
        ));

        self::assertEquals('bar', $context->var('foo'));
        self::assertEquals(0, $this->reportManager()->groups()->reports()->infos()->count(), 'Warning publshed');
    }

    public function testDoesNotExecuteTaskOnFalsePredicate(): void
    {
        $context = $this->runTask(new ConditionalTask(
            predicate: function (Context $context): bool {
                return false;
            },
            task: new ClosureTask(fn (Context $context) => new Success($context->withVar('foo', 'bar')))
        ));

        self::assertNull($context->var('foo'));
        self::assertEquals(1, $this->reportManager()->groups()->reports()->infos()->count(), 'Warning publshed');
    }
}
