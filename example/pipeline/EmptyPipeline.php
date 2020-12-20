<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Inventory\MainNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;

class EmptyPipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new GroupFact('my-reporting-group'),
            new NullTask()
        ]);
    }
}
