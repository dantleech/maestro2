<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Does nothing.
 *
 * This task can be used as a placeholder.
 */
class NullTask implements Task, Stringable
{
    public function __toString(): string
    {
        return 'Did nothing';
    }
}
