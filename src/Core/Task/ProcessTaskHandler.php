<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use function Amp\call;

class ProcessTaskHandler implements Handler
{
    public function __construct(
        private ProcessRunner $processRunner
    ) {
    }

    public function taskFqn(): string
    {
        return ProcessTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ProcessTask);
        return call(function (string $cwd) use ($task, $context) {
            yield $this->processRunner->mustRun(
                $task->args(),
                $cwd
            );

            return $context;
        }, $task->cwd() ?: $context->fact(CwdFact::class)->cwd());
    }

    private function formatArgs(ProcessTask $task): string
    {
        return implode(' ', array_map('escapeshellarg', $task->args()));
    }
}
