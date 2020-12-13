<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;
use Maestro2\Core\Task\Exception\TaskError;
use function Amp\call;

class GitCommitHandler implements Handler
{
    private ProcessRunner $runner;
    private ReportPublisher $publisher;

    public function __construct(ProcessRunner $runner, ReportPublisher $publisher)
    {
        $this->runner = $runner;
        $this->publisher = $publisher;
    }

    public function taskFqn(): string
    {
        return GitCommitTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof GitCommitTask);
        return call(function (string $cwd) use ($task, $context) {
            $result = yield $this->runner->run([
                'git',
                'rev-parse',
                '--show-toplevel',
            ], $cwd);
            assert($result instanceof ProcessResult);

            if (!$result->isOk()) {
                throw new TaskError(sprintf(
                    'Path "%s" is not a git repository',
                    $cwd
                ));
            }

            (function (string $topLevelPath) use ($task, $cwd) {
                if ($topLevelPath === $cwd) {
                    return;
                }
                throw new TaskError(sprintf(
                    'Path "%s" is not the root of a git repository (root is "%s")',
                    $cwd,
                    $topLevelPath
                ));
            })(trim($result->stdOut()));

            $result = yield $this->runner->mustRun(array_merge([
                'git',
                'ls-files',
                '-m',
            ], $task->paths()), $cwd);

            if ($result->stdOut() === '') {
                $this->publisher->publish(
                    $task->group() ?: $context->fact(GroupFact::class)->group(),
                    Report::warn(sprintf('Git commit "%s": no files modiied', $task->message()))
                );
                return $context;
            }

            yield $this->runner->mustRun(
                array_merge([
                    'git', 'add'
                ], $task->paths()),
                $cwd,
            );

            yield $this->runner->mustRun([
                'git', 'commit', '-m', $task->message()
            ], $cwd);

            return $context;
        }, $task->cwd() ?: $context->fact(CwdFact::class)->cwd());
    }
}
