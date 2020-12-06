<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;

class NullTaskHandler implements Handler
{
    public function taskFqn(): string
    {
        return NullTask::class;
    }

    public function run(Task $task): Promise
    {
        return new Success();
    }
}