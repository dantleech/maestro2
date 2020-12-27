<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\Success;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\Context;
use PHPUnit\Framework\TestCase;
use stdClass;

class ClosureHandlerTest extends HandlerTestCase
{
    public function testRunsClosure(): void
    {
        $context = $this->runTask(new ClosureTask(fn (Context $context) => new Success($context->withVar('foo', 'bar'))));

        self::assertEquals('bar', $context->var('foo'));
    }

    public function testRunsClosureReturnContextOnly(): void
    {
        $context = $this->runTask(new ClosureTask(fn (Context $context) => $context->withVar('foo', 'bar')));

        self::assertEquals('bar', $context->var('foo'));
    }

    public function testErrorIfNotReturningContext(): void
    {
        $this->expectException(RuntimeException::class);
        $context = $this->runTask(new ClosureTask(fn (Context $context) => new stdClass()));

        self::assertEquals('bar', $context->var('foo'));
    }
}
