<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Fact\Fact;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Exception\SequentialTaskError;
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
                if ($sequentialTask instanceof Fact) {
                    $context = $context->withFact($sequentialTask);
                    continue;
                }
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
