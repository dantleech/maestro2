<?php

namespace Maestro\Core\Task;

use Maestro\Core\Fact\Fact;

/**
 * Run a set of tasks in sequence.
 *
 * Use this task to run a series of other tasks, the context
 * returned from each task will be passed to the next.
 *
 * If the task leaves a `result`, it is only available to the next task.
 */
class SequentialTask implements Task
{
    /**
     * @param list<Task|Fact> $tasks Tasks to run in sequence
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
