<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Generator;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Task\Exception\TaskError;
use Stringable;
use Throwable;
use function Amp\call;

class SequentialTaskHandler implements Handler
{
    public function __construct(private Enqueuer $taskEnqueuer, private ReportPublisher $reportPublisher)
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
            foreach ($task->tasks() as $task) {
                try {
                    $context = yield $this->runTask($context, $task);
                    if ($task instanceof Stringable) {
                        $this->reportPublisher->publish(
                            ($context->factOrNull(GroupFact::class)?->group() ?: 'sequential'),
                            Report::ok($task->__toString())
                        );
                    }
                } catch (Throwable $taskError) {
                    $this->reportPublisher->publish(
                        ($context->factOrNull(GroupFact::class)?->group() ?: 'sequential'),
                        Report::fail(
                            $task instanceof Stringable ? $task->__toString() : $task::class,
                            $taskError->getMessage()
                        )
                    );
                    break;
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
