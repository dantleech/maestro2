<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Closure;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class ConditionalHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    public function taskFqn(): string
    {
        return ConditionalTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ConditionalTask);
        return call(function () use ($task, $context) {
            return (function (Closure $predicate, Task $task) use ($context) {
                if ($predicate($context)) {
                    $context = yield $this->enqueuer->enqueue(
                        TaskContext::create($task, $context)
                    );
                }

                return $context;
            })($task->predicate(), $task->task());
        });
    }
}
