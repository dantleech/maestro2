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
    public function __construct(private Closure $closure)
    {
    }

    /**
     * @return Closure(array<mixed>,Context)
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
