<?php

namespace Maestro2\Core\Task;

use Amp\Promise;
use Maestro2\Core\Queue\Enqueuer;
use function Amp\call;

class GitRepositoryHandler implements Handler
{
    public function __construct(private Enqueuer $enqueuer)
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

            yield $this->enqueuer->enqueue(TaskContext::create(new ProcessTask(
                args: [
                    'git',
                    'clone',
                    '--depth=1',
                    $task->url(),
                    $task->path()
                ]
            ), $context));

            return $context;
        });
    }
}
