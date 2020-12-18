<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\TaskReportPublisher;
use Throwable;
use function Amp\Promise\any;
use function Amp\call;

class ParallelHandler implements Handler
{
    public function __construct(
        private Enqueuer $taskEnqueuer,
        private TaskReportPublisher $publisher
    ) {
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
            foreach ($task->tasks() as $parallelTask) {
                $promises[] = $this->taskEnqueuer->enqueue(
                    TaskContext::create(
                        $parallelTask,
                        $context
                    )
                );
            }

            $results = yield any($promises);
            assert(is_array($results));

            /** @var int $index */
            foreach ($results[0] as $index => $error) {
                assert($error instanceof Throwable);
                $this->publisher->taskFail(array_values($task->tasks())[$index], $context, $error);
            }

            /** @var Context $taskContext */
            foreach ($results[1] as $taskContext) {
                $this->publisher->taskOk($task, $context);
                $context = $context->merge($taskContext);
            }

            return $context;
        });
    }
}
