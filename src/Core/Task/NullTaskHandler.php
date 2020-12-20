<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;

class NullTaskHandler implements Handler
{
    public function taskFqn(): string
    {
        return NullTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        return new Success($context);
    }
}
