<?php

namespace Maestro2\Core\Task;

class SequentialTask implements Task
{
    /**
     * @param list<Task> $tasks
     */
    public function __construct(private array $tasks)
    {
    }

    /**
     * @return list<Task>
     */
    public function tasks(): array
    {
        return $this->tasks;
    }
}
