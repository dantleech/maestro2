<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Task\Exception\HandlerNotFound;

class HandlerFactory
{
    public function __construct(private array $handlers)
    {
    }

    public function handlerFor($task): Handler
    {
        foreach ($this->handlers as $handler) {
            if ($handler->taskFqn() === $task::class) {
                return $handler;
            }
        }

        throw HandlerNotFound::forTask($task::class);
    }
}
