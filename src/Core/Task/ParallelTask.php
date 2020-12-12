<?php

namespace Maestro2\Core\Task;

class ParallelTask implements Task
{
    public function __construct(private array $tasks)
    {
    }

    public function tasks(): array
    {
        return $this->tasks;
    }
}
