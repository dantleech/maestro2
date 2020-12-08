<?php

namespace Maestro2\Core\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Task\Task;

interface RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task;
}
