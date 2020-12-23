<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Filesystem\Filesystem;

class ChangeDirectoryHandler implements Handler
{
    public function __construct(private Filesystem $filesystem)
    {
    }

    public function taskFqn(): string
    {
        return ChangeDirectoryTask::class;
    }

    public function run(Task $task, Context $context): Promise
    {
        assert($task instanceof ChangeDirectoryTask);
        return new Success($context->withService(
            $this->filesystem->cd($task->path())
        ));
    }
}
