<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use function Amp\call;

class GitDiffHandler implements Handler
{
    public function __construct(private ProcessRunner $runner, private ReportPublisher $publisher)
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
        return call(function () use ($task, $context) {
            $result = yield $this->runner->mustRun([
                'git',
                'diff',
            ], $context->fact(CwdFact::class)->cwd());

            $this->publisher->publish(
                $context->fact(GroupFact::class)->group(),
                Report::info('git diff', $result->stdOut())
            );

            return $context;
        });
    }
}
