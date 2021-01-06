<?php

namespace Maestro\Core\Task;

use Closure;

/**
 * Map a values to tasks.
 *
 * Use this to add a _sequence_ of tasks to the pipeline for each item of a given array.
 *
 * ```php
 * new MapTask(
 *     factory: fn(string $var) => new NullTask(),
 *     array: ['one', 'two'],
 * );
 * ```
 */
class MapTask implements Task
{
    /**
     * @param Closure(array<mixed,mixed>): Task factory should return a `Task` for each given item.
     * @param array $array Pass each item of this array to the factory.
     */
    public function __construct(private Closure $factory, private array $array)
    {
    }

    /**
     * @return Closure(array<mixed,mixed>): Task
     */
    public function factory(): Closure
    {
        return $this->factory;
    }

    public function array(): array
    {
        return $this->array;
    }
}
