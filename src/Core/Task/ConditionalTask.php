<?php

namespace Maestro\Core\Task;

use Closure;

/**
 * Execute another task conditionally
 *
 * ```php
 * new ConditionalTask(
 *     predicate: true,
 *     task: new NullTask()
 * );
 * ```
 *
 * You can also specify a message which will be shown:
 *
 * ```php:task
 * new ConditionalTask(
 *     predicate: fn () => false,
 *     task: new NullTask(),
 *     message: "Did not do nothing because false"
 * );
 * ```
 */
class ConditionalTask implements Task
{
    /**
     * @param Closure(Context): bool Predicate, specify `true` to execute task, `false` otherwise.
     * @param Task $task Task to execute if predicate is `true`
     * @param ?string $message Message to show
     */
    public function __construct(private Closure $predicate, private Task $task, private ?string $message = null)
    {
    }

    /**
     * @return Closure(Context): bool
     */
    public function predicate(): Closure
    {
        return $this->predicate;
    }

    public function task(): Task
    {
        return $this->task;
    }

    public function message(): ?string
    {
        return $this->message;
    }
}
