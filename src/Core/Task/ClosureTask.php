<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;

class ClosureTask implements Task, Stringable
{
    /**
     * @param Closure(array<mixed>,Context) $closure
     * @param list<mixed> $args
     */
    public function __construct(private Closure $closure, private array $args = [])
    {
    }

    /**
     * @return Closure(array<mixed>,Context)
     */
    public function closure(): Closure
    {
        return $this->closure;
    }

    /**
     * @return list<mixed> $args
     */
    public function args(): array
    {
        return $this->args;
    }

    public function __toString(): string
    {
        return 'Running closure';
    }
}
