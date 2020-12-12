<?php

namespace Maestro2\Core\Queue;

use Amp\Deferred;
use Amp\Promise;
use Maestro2\Core\Task\TaskContext;
use Throwable;

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

    public function resolve(TaskContext $task, mixed $result, Throwable $error = null): void
    {
        $hash = spl_object_hash($task);
        (function (Deferred $deferred) use ($result, $error) {
            if ($error) {
                $deferred->fail($error);
                return;
            }

            $deferred->resolve($result);
        })($this->promises[$hash]);
        unset($this->promises[$hash]);
    }
}
