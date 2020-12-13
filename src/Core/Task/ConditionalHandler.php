<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Closure;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Stringable;
use function Amp\call;

class ConditionalHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer, private ReportPublisher $publusher)
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

                $this->publusher->publish(
                    $context->factOrNull(GroupFact::class)?->group() ?: 'conditional',
                    Report::warn(sprintf(
                        'Did not execute task "%s" due to predicate',
                        ($task instanceof Stringable) ? $task->__toString() : $task::class
                    ))
                );

                return $context;
            })($task->predicate(), $task->task());
        });
    }
}
