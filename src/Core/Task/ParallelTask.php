<?php

namespace Maestro\Core\Task;

/**
 * Execute multiple tasks in parallel
 *
 *
 * For example to checkout multiple repsoitories at the same time:
 *
 * ```
 * new ParallelTask([
 *     new RepositoryTask(url: 'https://example.com/foobar/barfoo', 'barfoo'),
 *     new RepositoryTask(url: 'https://example.com/foobar/foobar', 'foobar'),
 * ])
 * ```
 *
 * Typically you would use this task to setup pipelines in parallel:
 *
 * new ParallelTask([
 *     new SequenceTask(
 *       // other tasks
 *     ),
 *     new SequenceTask(
 *       // other tasks
 *     ),
 * ])
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
