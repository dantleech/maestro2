<?php

namespace Maestro2\Core\Stage;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Task\Task;

interface RepositoryStage
{
    public function build(RepositoryNode $repository): Task;
}
