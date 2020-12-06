<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Maestro2\Tests\IntegrationTestCase;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;

abstract class HandlerTestCase extends IntegrationTestCase
{
    abstract protected function createHandler(): Handler;

    protected function runTask(Task $fileTask)
    {
        return wait((new HandlerFactory([
            $this->createHandler()
        ]))->handlerFor($fileTask)->run($fileTask));
    }
}
