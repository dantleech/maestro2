<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use function Amp\call;

class ProcessTaskHandler implements Handler
{
    public function __construct(
        private ProcessRunner $processRunner,
        private ReportPublisher $reportPublisher
    ) {
    }

    public function taskFqn(): string
    {
        return ProcessTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof ProcessTask);
        return call(function () use ($task) {
            $result = yield $this->processRunner->run($task->args(), $task->cwd());

            $this->publishReport($task, $result);
            return $result;
        });
    }

    private function publishReport(ProcessTask $task, ProcessResult $result): void
    {
        match ($result->exitCode()) {
            0 => $this->reportPublisher->publish('result', Report::ok(sprintf(
                    '%s exited with code %s',
                    implode(' ', $task->args()),
                    $result->exitCode()
            ))),
            default => $this->reportPublisher->publish('result', Report::fail(sprintf(
                    '%s exited with code %s',
                    implode(' ', $task->args()),
                    $result->exitCode()
            ))),
        };
    }
}
