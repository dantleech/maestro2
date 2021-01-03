<?php

namespace Maestro\Git\Task;

use Maestro\Core\Task\Task;
use Stringable;

class GitSurveyTask implements Task, Stringable
{
    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return 'Performing GiT survey';
    }
}
