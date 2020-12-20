<?php

namespace Maestro\Core\Pipeline;

use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\Task;

class NullPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new NullTask();
    }
}
