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

    public function run(Task $task, Context $context): Promise
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
            0 => $this->reportPublisher->publish($task->group(), Report::ok(sprintf(
                    '%s exited with code %s',
                    $this->formatArgs($task),
                    $result->exitCode()
            ))),
            default => $this->reportPublisher->publish($task->group(), Report::fail(
                title: sprintf(
                    '%s in "%s" exited with code %s',
                    $this->formatArgs($task),
                    $task->cwd() ?? '<none>',
                    $result->exitCode()
                ),
                body: sprintf("OUT: \n%s\nERR: %s", $result->stdOut(), $result->stdErr())
            )),
        };
    }

    private function formatArgs(ProcessTask $task): string
    {
        return implode(' ', array_map('escapeshellarg', $task->args()));
    }
}
