<?php

namespace Maestro\Core\Task;

use Closure;

/**
 * Map a values to tasks.
 *
 * Use this to add a _sequence_ of tasks to the pipeline for each
 * set of values.
 */
class MapTask implements Task
{
    /**
     * @param Closure(array<mixed,mixed>): Task $factory
     */
    public function __construct(private Closure $factory, private array $vars)
    {
    }

    /**
     * @return Closure(array<mixed,mixed>): Task
     */
    public function factory(): Closure
    {
        return $this->factory;
    }

    public function vars(): array
    {
        return $this->vars;
    }
}
