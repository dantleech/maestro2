<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

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
                $context = yield $this->enqueuer->enqueue(
                    TaskContext::create(new ProcessTask(
                        group: $context->fact(GroupFact::class)->group(),
                        cmd: $command
                    ), $context)
                );

                /** @var ProcessResult $result */
                $result = $context->result();

                if ($task->failFast() && false === $result->isOk()) {
                    break;
                }
            }

            return $context;
        });
    }
}
