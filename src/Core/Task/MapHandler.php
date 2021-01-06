<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Queue\TaskRunner;
use function Amp\call;

class MapHandler implements Handler
{
    public function __construct(private Enqueuer $queue)
    {
    }
    public function taskFqn(): string
    {
        return MapTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof MapTask);

        return call(function () use ($task, $context) {
            $factory = $task->factory();

            $context = yield $this->queue->enqueue(new TaskContext(
                new SequentialTask(array_values(array_map($factory, $task->vars()))),
                $context
            ));

            return new Success($context);
        });
    }
}
