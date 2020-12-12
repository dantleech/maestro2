<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;

class ClosureTask implements Task, Stringable
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

    public function __toString(): string
    {
        return 'Running closure';
    }
}
