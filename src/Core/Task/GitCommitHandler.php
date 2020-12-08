<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Process\ProcessRunner;
use function Amp\call;

class GitCommitHandler implements Handler
{
    private ProcessRunner $runner;

    public function __construct(ProcessRunner $runner)
    {
        $this->runner = $runner;
    }

    public function taskFqn(): string
    {
        return GitCommitTask::class;
    }

    public function run(Task $task): Promise
    {
        assert($task instanceof GitCommitTask);
        return call(function () use ($task) {
            yield $this->runner->mustRun(
                args: array_merge([
                    'git', 'add'
                ], $task->paths()),
                cwd: $task->cwd(),
            );
            yield $this->runner->mustRun([
                'git', 'commit', '-m', $task->message()
            ], $task->cwd());
        });
    }
}
