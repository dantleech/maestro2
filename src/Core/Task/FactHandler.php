<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;

class FactHandler implements Handler
{
    public function taskFqn(): string
    {
        return FactTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof FactTask);
        return new Success($context->withFact($task->fact()));
    }
}
