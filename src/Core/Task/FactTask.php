<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Fact\Fact;
use Stringable;

class FactTask implements Task, Stringable
{
    public function __construct(private Fact $fact)
    {
    }

    public function fact(): Fact
    {
        return $this->fact;
    }

    public function __toString(): string
    {
        return sprintf('Establshing fact "%s"', $this->fact::class);
    }
}
