<?php

namespace Maestro2\Core\Task;

use Amp\Promise;

class ClosureHandler implements Handler
{
    public function taskFqn(): string
    {
        return ClosureTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ClosureTask);
        return ($task->closure())($task->args(), $context);
    }
}
