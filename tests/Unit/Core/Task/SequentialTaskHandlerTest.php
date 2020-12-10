<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Task\ClosureHandler;
use Maestro2\Core\Task\ClosureTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\SequentialTaskHandler;
use Maestro2\Core\Task\Task;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;

class SequentialTaskHandlerTest extends HandlerTestCase
{
    protected function createHandler(): Handler
    {
        return new SequentialTaskHandler(new TestEnqueuer(
            new HandlerFactory([
                new ClosureHandler()
            ])
        ));
    }

    public function testRunsTasksSequentially(): void
    {
        self::assertEquals(3, $this->runTask(new SequentialTask([
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success($context->set('count', 1));
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->set('count', $context->var('count') + 1)
                );
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->set('count', $context->var('count') + 1)
                );
            }),
        ], new Context([])))->var('count'));
    }
}
