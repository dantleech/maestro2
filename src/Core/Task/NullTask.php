<?php

namespace Maestro2\Core\Task;

use Stringable;

class NullTask implements Task, Stringable
{
    public function __toString(): string
    {
        return 'Did nothing';
    }
}
