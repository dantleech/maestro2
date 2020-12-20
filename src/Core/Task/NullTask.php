<?php

namespace Maestro\Core\Task;

use Stringable;

class NullTask implements Task, Stringable
{
    public function __toString(): string
    {
        return 'Did nothing';
    }
}
