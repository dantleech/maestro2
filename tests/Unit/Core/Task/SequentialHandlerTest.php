<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Exception\SequentialTaskError;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\SequentialTask;
use RuntimeException;

class SequentialHandlerTest extends HandlerTestCase
{
    public function testRunsTasksSequentially(): void
    {
        self::assertEquals(3, $this->runTask(new SequentialTask([
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success($context->withVar('count', 1));
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('count', $context->var('count') + 1)
                );
            }),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('count', $context->var('count') + 1)
                );
            }),
        ], Context::create()))->var('count'));
    }

    public function testFailsEarlyAndPublishesAReport(): void
    {
        try {
            $context = $this->runTask(new SequentialTask([
                new ClosureTask(function (array $args, Context $context): Promise {
                    return new Success($context->withVar('count', 1));
                }),
                new ClosureTask(function (array $args, Context $context): Promise {
                    throw new RuntimeException('Oh dear!!');
                }),
                new ClosureTask(function (array $args, Context $context): Promise {
                    return new Success($context->withVar('count', $context->var('count') + 1));
                }),
            ]));
        } catch (TaskError $error) {
        }

        self::assertNotNull($error, 'Exception was thrown');
        self::assertInstanceOf(SequentialTaskError::class, $error, 'Correct error type thrown');

        self::assertCount(1, $this->reportManager()->group(self::EX_GROUP)->reports()->fails(), 'Published failure report');
    }

    public function testDoesNotPublishReportForSequentialTaskErrors(): void
    {
        try {
            $context = $this->runTask(new SequentialTask([
                new ClosureTask(function (array $args, Context $context): Promise {
                    throw new SequentialTaskError('Oh dear!!');
                }),
            ]));
        } catch (TaskError $error) {
        }

        self::assertInstanceOf(SequentialTaskError::class, $error, 'Correct error type thrown');

        self::assertCount(0, $this->reportManager()->groups()->reports()->fails(), 'Did not publish failure report');
    }

    public function testAssimilatesFactsAndContinues(): void
    {
        $context = $this->runTask(new SequentialTask([
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success($context->withVar('count', 1));
            }),
            new CwdFact('foobar'),
            new ClosureTask(function (array $args, Context $context): Promise {
                return new Success(
                    $context->withVar('count', $context->var('count') + 1)
                );
            })
        ]));

        self::assertEquals('foobar', $context->fact(CwdFact::class)->cwd());
    }
}
