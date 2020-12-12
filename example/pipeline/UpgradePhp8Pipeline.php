<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;

class UpgradePhp8Pipeline extends BasePipeline
{
    public function buildRepository(RepositoryNode $repository): Task
    {
        return new SequentialTask([
        ]);
    }
}
