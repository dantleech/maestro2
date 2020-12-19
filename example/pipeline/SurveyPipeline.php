<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\GitSurveyTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\Task;

class SurveyPipeline extends BasePipeline
{
    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new GitSurveyTask();
    }
}
