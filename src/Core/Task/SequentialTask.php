<?php

namespace Maestro2\Core\Task;

class SequentialTask implements Task
{
    public function __construct(private array $tasks)
    {
    }

    public function tasks()
    {
        return $this->tasks;
    }
}
