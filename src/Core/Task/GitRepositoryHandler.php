<?php

namespace Maestro\Core\Task;

use Amp\Promise;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Filesystem\Filesystem;
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
        return call(function (Filesystem $filesystem) use ($task, $context) {
            assert($task instanceof GitRepositoryTask);

            if ($filesystem->exists($task->path())) {
                yield $this->handleExisting($task, $context);
                return $context;
            }

            $args = [
                'git',
                'clone',
                $task->url(),
                $task->path()
            ];
            if ($task->branch()) {
                $args[] = '--branch';
                $args[] = $task->branch();
            }
            yield $this->enqueuer->enqueue(TaskContext::create(new ProcessTask(
                cmd: $args
            ), $context));

            return $context;
        }, $context->service(Filesystem::class));
    }

    private function handleExisting(GitRepositoryTask $task, Context $context): Promise
    {
        return call(function () use ($task, $context) {
            if ($task->clean()) {
                yield $this->enqueuer->enqueue(TaskContext::create(new ProcessTask(
                    cmd: [
                        'git',
                        'clean',
                        '-f',
                        '-d',
                    ]
                ), $context));
            }

        });

    }
}
