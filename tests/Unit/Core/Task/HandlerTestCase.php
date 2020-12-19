<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Maestro2\Tests\IntegrationTestCase;
use function Amp\Promise\wait;

abstract class HandlerTestCase extends IntegrationTestCase
{
    abstract protected function createHandler(): Handler;

    protected function defaultContext(): Context
    {
        return Context::create();
    }

    protected function runTask(Task $task, ?Context $context = null): Context
    {
        $context = $context ?: $this->defaultContext();

        return wait((new HandlerFactory([
            $this->createHandler()
        ]))->handlerFor($task)->run($task, $context)) ?: Context::create();
    }

    protected function filesystem(): Filesystem
    {
        return new Filesystem($this->workspace()->path());
    }
}
