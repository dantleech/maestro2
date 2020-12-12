<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Process\ProcessRunner;
use function Amp\call;

class GitRepositoryHandler implements Handler
{
    public function __construct(private ProcessRunner $runner, private WorkspacePathResolver $resolver)
    {
    }

    public function taskFqn(): string
    {
        return GitRepositoryTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        return call(function () use ($task, $context) {
            assert($task instanceof GitRepositoryTask);

            $path = $this->resolver->resolve($task->path());

            yield $this->runner->mustRun([
                'git',
                'clone',
                $task->url(),
                $path
            ]);

            return $context;
        });
    }
}
