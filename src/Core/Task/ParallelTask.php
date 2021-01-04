<?php

namespace Maestro\Core\Task;

/**
 * Execute multiple tasks in parallel
 *
 *
 * For example to checkout multiple repsoitories at the same time:
 *
 * ```php:task
 * new ParallelTask([
 *     // repository 1,
 *     // repository 2,
 *
 * ])
 * ```
 *
 * Typically you would use this task to setup pipelines in parallel:
 *
 * ```php:task
 * new ParallelTask([
 *     new SequentialTask([
 *       // other tasks
 *     ]),
 *     new SequentialTask([
 *       // other tasks
 *     ]),
 * ])
 * ```
 */
class ParallelTask implements Task
{
    /**
     * @param array<array-key,Task> $tasks Tasks to run in parallel
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
