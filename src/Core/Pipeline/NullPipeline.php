<?php

namespace Maestro2\Core\Pipeline;

use Maestro2\Core\Inventory\MainNode;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\Task;

class NullPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new NullTask();
    }
}
