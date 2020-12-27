<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Exception\RuntimeException;

class ClosureHandler implements Handler
{
    public function taskFqn(): string
    {
        return ClosureTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ClosureTask);
        $result = ($task->closure())($context);

        if ($result instanceof Promise) {
            return $result;
        }

        if (!$result instanceof Context) {
            throw new RuntimeException(sprintf(
                'Closure must return the given Context, it returned "%s"',
                is_object($result) ? $result::class : gettype($result)
            ));
        }

        return new Success($result);
    }
}
