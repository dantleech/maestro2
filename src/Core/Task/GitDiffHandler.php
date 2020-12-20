<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;
use function Amp\call;

class GitDiffHandler implements Handler
{
    public function __construct(private Filesystem $filesystem, private ProcessRunner $runner, private ReportPublisher $publisher)
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

            $this->publisher->publish(
                $context->fact(GroupFact::class)->group(),
                Report::info('git diff', $result->stdOut())
            );

            return $context;
        }, $this->filesystem->cd(
            $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
        )->localPath());
    }
}
