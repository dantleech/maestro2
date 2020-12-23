<?php

namespace Maestro\Core\Task;

use Maestro\Core\Task\Exception\HandlerNotFound;

class HandlerFactory
{
    public function __construct(private array $handlers)
    {
    }

    public function handlerFor(Task $task): Handler
    {
        foreach ($this->handlers as $handler) {
            assert($handler instanceof Handler);
            $fqn = $handler->taskFqn();
            if ($task instanceof $fqn) {
                return $handler;
            }
        }

        throw HandlerNotFound::forTask($task::class);
    }
}
