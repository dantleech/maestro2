<?php

namespace Maestro2\Core\Task;

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
