<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\SetDirectoryTask;

class SetDirectoryHandler implements Handler
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function taskFqn(): string
    {
        return SetDirectoryTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof SetDirectoryTask);
        return new Success($context->withService(
            $this->filesystem->cd($task->path())
        ));
    }
}
