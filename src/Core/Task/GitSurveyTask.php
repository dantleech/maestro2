<?php

namespace Maestro2\Core\Task;

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
