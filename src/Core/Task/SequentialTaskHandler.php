<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class SequentialTaskHandler implements Handler
{
    public function __construct(private Enqueuer $taskEnqueuer)
    {
    }

    public function taskFqn(): string
    {
        return SequentialTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof SequentialTask);
        $this->taskEnqueuer->enqueueAll($task->tasks());

        return new Success();
    }
}
