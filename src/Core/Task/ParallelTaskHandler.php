<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\Promise\all;
use function Amp\call;

class ParallelTaskHandler implements Handler
{
    public function __construct(private Enqueuer $taskEnqueuer)
    {
    }

    public function taskFqn(): string
    {
        return ParallelTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ParallelTask);
        return call(function () use ($task, $context) {
            $promises = [];
            foreach ($task->tasks() as $task) {
                $promises[] = $this->taskEnqueuer->enqueue(
                    TaskContext::create(
                        $task,
                        $context
                    )
                );
            }

            $taskContexts = yield all($promises);

            foreach ($taskContexts as $taskContext) {
                $context = $context->merge($taskContext);
            }

            return $context;
        });
    }
}
