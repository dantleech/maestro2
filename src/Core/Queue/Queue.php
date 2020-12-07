<?php

namespace Maestro2\Core\Queue;

use Amp\Deferred;
use Amp\Promise;
use Maestro2\Core\Task\Task;

class Queue implements Enqueuer, Dequeuer
{
    private array $promises = [];

    public function __construct(private array $tasks = [])
    {
    }

    public function enqueue(Task $task): Promise
    {
        $deferred = new Deferred();
        $this->promises[spl_object_hash($task)] = $deferred;
        $this->tasks[] = $task;

        return $deferred->promise();
    }

    public function dequeue(): ?Task
    {
        return array_shift($this->tasks);
    }

    /**
     * @param mixed $result
     */
    public function resolve(Task $task, $result): void
    {
        $hash = spl_object_hash($task);
        $this->promises[$hash]->resolve($result);
        unset($this->promises[$hash]);
    }
}
