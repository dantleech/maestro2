<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Fact\Fact;

class FactTask implements Task
{
    public function __construct(private Fact $fact) {
    }

    public function fact(): Fact
    {
        return $this->fact;
    }
}
