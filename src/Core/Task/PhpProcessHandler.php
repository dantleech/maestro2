<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Fact\PhpFact;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class PhpProcessHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    public function taskFqn(): string
    {
        return PhpProcessTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof PhpProcessTask);
        return $this->enqueuer->enqueue(
            TaskContext::create(new ProcessTask(
                cmd: array_merge([
                    $context->factOrNull(PhpFact::class)?->phpBin() ?: PHP_BINARY
                ], $task->cmd()),
                after: $task->after(),
                allowFailure: $task->allowFailure(),
            ), $context)
        );
    }
}
