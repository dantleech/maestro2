<?php

namespace Maestro\Core\Task;

use Maestro\Core\Fact\Fact;

class SequentialTask implements Task
{
    /**
     * @param list<Task|Fact> $tasks
     */
    public function __construct(private array $tasks)
    {
    }

    /**
     * @return list<Task|Fact>
     */
    public function tasks(): array
    {
        return $this->tasks;
    }
}
