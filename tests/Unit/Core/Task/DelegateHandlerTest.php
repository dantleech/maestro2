<?php

namespace Maestro\Tests\Unit\Core\Task;

use Amp\Success;
use Maestro\Core\Task\ClosureTask;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\DelegateTask;
use Maestro\Core\Task\Task;

class DelegateHandlerTest extends HandlerTestCase
{
    public function testDelegateTask(): void
    {
        $context = $this->runTask(new TestDelegateTask());
        self::assertEquals('barfoo', $context->var('foobar'));
    }
}

class TestDelegateTask implements DelegateTask
{
    public function task(): Task
    {
        return new ClosureTask(function (Context $context) {
            return new Success($context->withVar('foobar', 'barfoo'));
        });
    }
}
