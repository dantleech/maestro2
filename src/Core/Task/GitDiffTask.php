<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Publish a GIT diff
 *
 * Use this task to display the GIT diff for inspection.
 *
 * ```php
 * new GitDiff();
 * ```
 */
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
