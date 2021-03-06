<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use function Amp\call;

class GitDiffHandler implements Handler
{
    public function __construct(private ProcessRunner $runner)
    {
    }

    public function taskFqn(): string
    {
        return GitDiffTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        return call(function (string $cwd) use ($task, $context) {
            $result = yield $this->runner->mustRun([
                'git',
                'diff',
            ], $cwd);

            if ($result->stdOut()) {
                $context->service(TaskReportPublisher::class)->publish(
                    Report::info('git diff', $result->stdOut())
                );
            }

            return $context->withResult($result);
        }, $context->service(Filesystem::class)->localPath());
    }
}
