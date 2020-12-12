<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class SequentialTaskHandler implements Handler
{
    public function __construct(private Enqueuer $taskEnqueuer)
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
                $context = $context->merge((static function (?object $context) use ($task) {
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
            }

            return $context;
        });
    }
}
