<?php

namespace Maestro2\Core\Build;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\SequentialTask;

class BuildFactory
{
    public function __construct(private Enqueuer $queue, private Worker $worker)
    {
    }

    public function createBuild(MainNode $config): Build
    {
        $tasks = [
            new FileTask(
                type: 'directory',
                path: $config->workspacePath(),
                exists: false,
            ),
        ];
        foreach ($config->repositories() as $repository) {
            $tasks[] = new SequentialTask([
                new FileTask(
                    type: 'directory',
                    path: $config->workspacePath(),
                    mode: 0777,
                    exists: true,
                ),
                new GitRepositoryTask(
                    url: $repository->url(),
                    path: sprintf('%s/%s', $config->workspacePath(), $repository->name()),
                )
            ]);
        }

        return new Build($this->queue, $tasks, $this->worker);
    }
}
