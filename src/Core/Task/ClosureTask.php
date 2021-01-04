<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;

/**
 * Run an anonymous function into the pipeline.
 *
 * This task allows you to run an anonymous function. The function
 * accepts the `Context` and must return the `Context`.
 *
 * ```php:task
 * new ClosureTask(
 *     function (Context $context) {
 *         return $context;
 *     }
 * );
 * ```
 */
class ClosureTask implements Task, Stringable
{
    /**
     * @param Closure(Context): Context $closure
     */
    public function __construct(private Closure $closure)
    {
    }

    /**
     * @return Closure(Context)
     */
    public function closure(): Closure
    {
        return $this->closure;
    }

    public function __toString(): string
    {
        return 'Running closure';
    }
}
