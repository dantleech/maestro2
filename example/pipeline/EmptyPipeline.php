<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\SetReportingGroupTask;
use Maestro\Core\Task\Task;

class EmptyPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new NullTask(),
        ]);
    }
}
