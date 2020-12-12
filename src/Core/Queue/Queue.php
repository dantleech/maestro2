<?php

namespace Maestro2\Core\Queue;

use Amp\Deferred;
use Amp\Promise;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TaskContext;

class Queue implements Enqueuer, Dequeuer
{
    private array $promises = [];

    public function __construct(private array $tasks = [])
    {
    }

    public function enqueue(TaskContext $task): Promise
    {
        $deferred = new Deferred();
        $this->promises[spl_object_hash($task)] = $deferred;
        $this->tasks[] = $task;

        return $deferred->promise();
    }

    public function dequeue(): ?TaskContext
    {
        return array_shift($this->tasks);
    }

    /**
     * @param mixed $result
     */
    public function resolve(TaskContext $task, $result): void
    {
        $hash = spl_object_hash($task);
        $this->promises[$hash]->resolve($result);
        unset($this->promises[$hash]);
    }
}
