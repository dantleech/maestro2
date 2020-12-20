<?php

namespace Maestro\Core\Task;

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
