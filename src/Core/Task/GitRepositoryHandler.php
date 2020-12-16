<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Process\ProcessRunner;
use function Amp\call;

class GitRepositoryHandler implements Handler
{
    public function __construct(private Filesystem $filesystem, private ProcessRunner $runner, private WorkspacePathResolver $resolver)
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
                '--depth=1',
                $task->url(),
                $this->filesystem->localPath(
                    $context->factOrNull(CwdFact::class)?->cwd() ?: '/'
                )
            ]);

            return $context;
        });
    }
}
