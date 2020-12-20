<?php

namespace Maestro\Core\Task;

use Stringable;

class GitDiffTask implements Task, Stringable
{
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return 'Displaying git diff';
    }
}
