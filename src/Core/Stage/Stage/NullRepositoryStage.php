<?php

namespace Maestro2\Core\Pipeline\Repository;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\Task;

class NullRepositoryPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new NullTask();
    }
}
