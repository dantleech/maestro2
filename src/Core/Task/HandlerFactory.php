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
            if ($handler->taskFqn() === $task::class) {
                return $handler;
            }
        }

        throw HandlerNotFound::forTask($task::class);
    }
}
