<?php

namespace Maestro2\Core\Task;

use Closure;

class ClosureTask implements Task
{
    public function __construct(private Closure $closure, private array $args = [])
    {
    }

    public function closure(): Closure
    {
        return $this->closure;
    }

    public function args(): array
    {
        return $this->args;
    }
}
