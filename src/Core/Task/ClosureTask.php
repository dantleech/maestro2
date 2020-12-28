<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;

class ClosureTask implements Task, Stringable
{
    /**
     * @param Closure(Context) $closure
     * @param list<mixed> $args
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
