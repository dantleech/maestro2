<?php

namespace Maestro2\Core\Task;

class ParallelTask implements Task
{
    /**
     * @param array<array-key,Task> $tasks
     */
    public function __construct(private array $tasks)
    {
    }

    /**
     * @return array<array-key,Task>
     */
    public function tasks(): array
    {
        return $this->tasks;
    }
}
