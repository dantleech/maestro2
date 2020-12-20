<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Queue\Enqueuer;
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
                cmd: [
                    'git',
                    'clone',
                    $task->url(),
                    $task->path()
                ]
            ), $context));

            return $context;
        });
    }
}
