<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;

class ClosureTask implements Task, Stringable
{
    /**
     * @param Closure(array<mixed>,Context) $closure
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

    public function args(): array
    {
        return $this->args;
    }

    public function __toString(): string
    {
        return 'Running closure';
    }
}
