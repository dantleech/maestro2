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
        return call(function (string $cwd) use ($task, $context) {
            $result = yield $this->processRunner->run(
                $task->args(),
                $cwd
            );

            $this->publishReport(
                $task,
                $task->group() ?: $context->fact(GroupFact::class)->group(),
                $cwd,
                $result
            );

            return $context;
        }, $task->cwd() ?: $context->fact(CwdFact::class)->cwd());
    }

    private function publishReport(ProcessTask $task, string $group, string $cwd, ProcessResult $result): void
    {
        match ($result->exitCode()) {
            0 => $this->reportPublisher->publish(
                $group,
                Report::ok(sprintf(
                    '%s in "%s" exited with code %s',
                    $this->formatArgs($task),
                    $cwd,
                    $result->exitCode()
                ))
            ),
            default => $this->reportPublisher->publish($group, Report::fail(
                title: sprintf(
                    '%s in "%s" exited with code %s',
                    $this->formatArgs($task),
                    $cwd,
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
