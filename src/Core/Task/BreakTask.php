<?php

namespace Maestro\Core\Task;

use Closure;

class BreakTask
{
    public function __construct(private Closure $predicate)
    {
    }

    public function predicate(): Closure
    {
        return $this->predicate;
    }
}
