<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\TaskReportPublisher;
use Maestro2\Core\Task\Exception\SequentialTaskError;
use Maestro2\Core\Task\Exception\TaskError;
use Throwable;
use function Amp\call;

class SequentialHandler implements Handler
{
    public function __construct(private Enqueuer $taskEnqueuer, private TaskReportPublisher $reportPublisher)
    {
    }

    public function taskFqn(): string
    {
        return SequentialTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof SequentialTask);
        return call(function () use ($task, $context) {
            foreach ($task->tasks() as $sequentialTask) {
                try {
                    $context = yield $this->runTask($context, $sequentialTask);
                    $this->reportPublisher->taskOk($sequentialTask, $context);
                } catch (Throwable $error) {
                    if (!$error instanceof SequentialTaskError) {
                        $this->reportPublisher->taskFail($sequentialTask, $context, $error);
                    }

                    throw new SequentialTaskError(sprintf(
                        'Task sequence failed (last task: "%s"): %s',
                        TaskUtil::describe($task),
                        $error->getMessage()
                    ), 0, $error);
                }
            }

            return $context;
        });
    }

    /**
     * @return Promise<Context>
     */
    private function runTask(Context $context, Task $task): Promise
    {
        return call(function () use ($context, $task) {
            return $context->merge((static function (?object $context) use ($task) {
                if (null === $context) {
                    return null;
                }

                if (!$context instanceof Context) {
                    throw new RuntimeException(sprintf(
                        'Task handler for "%s" did not return a Context, it returned a "%s"',
                        $task::class,
                        $context::class
                    ));
                }

                return $context;
            })(yield $this->taskEnqueuer->enqueue(
                TaskContext::create(
                    $task,
                    $context
                )
            )));
        });
    }
}
