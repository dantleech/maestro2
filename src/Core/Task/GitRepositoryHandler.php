<?php

namespace Maestro2\Core\Task;

use Amp\Process\Process;
use Amp\Promise;
use Maestro2\Core\Process\ProcessRunner;
use function Amp\ByteStream\buffer;
use function Amp\call;

class GitRepositoryHandler implements Handler
{
    public function __construct(private ProcessRunner $runner)
    {
    }

    public function taskFqn(): string
    {
        return GitRepositoryTask::class;
    }

    public function run(Task $task): Promise
    {
        return call(function () use ($task) {
            assert($task instanceof GitRepositoryTask);
            yield $this->runner->mustRun([
                'git',
                'clone',
                $task->url(),
                $task->path()
            ]);
        });
    }
}
