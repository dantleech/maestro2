<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Closure;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use Stringable;
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
            return (function (Closure $predicate, Task $conditionalTask) use ($context, $task) {
                if ($predicate($context)) {
                    $context = yield $this->enqueuer->enqueue(
                        TaskContext::create($conditionalTask, $context)
                    );
                } else {
                    $context->service(TaskReportPublisher::class)->publish(
                        Report::info(sprintf(
                            $task->message() ?: 'Did not execute task "%s" due to predicate',
                            ($conditionalTask instanceof Stringable) ? $conditionalTask->__toString() : $conditionalTask::class
                        ))
                    );
                }

                return $context;
            })($task->predicate(), $task->task());
        });
    }
}
