<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;
use Maestro2\Core\Task\ProcessesTask;

class ProcessesHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    public function taskFqn(): string
    {
        return ProcessesTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ProcessesTask);
        return call(function () use ($task, $context) {
            foreach ($task->commands() as $command) {
                $result = yield $this->enqueuer->enqueue(
                    TaskContext::create(new ProcessTask(
                        group: $context->fact(GroupFact::class)->group(),
                        args: $command
                    ), $context)
                );

                if ($task->failFast() && false === $result->isOk()) {
                    break;
                }
            }

            return $context;
        });
    }
}
