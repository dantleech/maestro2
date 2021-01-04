<?php

namespace Maestro\Git\Task;

use Maestro\Core\Task\Task;
use Stringable;

/**
 * Perform a GiT survey
 *
 * This task will collect and publish information about your GiT repository:
 *
 * - The latest tag
 * - The number of commits ahead of the latest tag
 * - The latest commit message
 *
 * It will leave a `GitSurveyFact`in the pipeline.
 */
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
