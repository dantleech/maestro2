<?php

namespace Maestro2\Core\Queue;

use Amp\Deferred;
use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\TaskContext;
use Throwable;
use Webmozart\Assert\Assert;

class Queue implements Enqueuer, Dequeuer
{
    /**
     * @var array<string,Deferred<Context>>
     */
    private array $deferred = [];

    /**
     * @param array<array-key,TaskContext> $tasks
     */
    public function __construct(private array $tasks = [])
    {
    }

    /**
     * @return Promise<Context>
     */
    public function enqueue(TaskContext $task): Promise
    {
        /** @var Deferred<Context> */
        $deferred = new Deferred();
        $this->deferred[spl_object_hash($task)] = $deferred;
        $this->tasks[] = $task;

        return $deferred->promise();
    }

    public function dequeue(): ?TaskContext
    {
        $context = array_shift($this->tasks);

        if (null === $context) {
            return null;
        }

        Assert::isInstanceOf($context, TaskContext::class);

        return $context;
    }

    public function resolve(TaskContext $task, ?Context $context, Throwable $error = null): void
    {
        $hash = spl_object_hash($task);
        (function (Deferred $deferred) use ($context, $error) {
            if ($error) {
                $deferred->fail($error);
                return;
            }

            $deferred->resolve($context);
        })($this->deferred[$hash]);
        unset($this->deferred[$hash]);
    }
}
