<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Queue\Enqueuer;

class DelegateTaskHandler implements Handler
{
    public function __construct(private Enqueuer $queue)
    {
    }

    public function taskFqn(): string
    {
        return DelegateTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof DelegateTask);
        return $this->queue->enqueue(new TaskContext($task->task(), $context));
    }
}
