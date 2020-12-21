<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Queue\Enqueuer;
use function Amp\call;
use function Clue\Arguments\split;

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
                ], $this->normalize($task->cmd())),
                after: $task->after(),
                allowFailure: $task->allowFailure(),
            ), $context)
        );
    }

    /**
     * @param list<string>|string $cmd
     * @return list<string>
     */
    private function normalize(array|string $cmd): array
    {
        if (is_array($cmd)) {
            return $cmd;
        }

        return array_values(split($cmd));
    }
}
