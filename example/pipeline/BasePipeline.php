<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ParallelTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;

abstract class BasePipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new FileTask(
                type: 'directory',
                path: $mainNode->workspacePath(),
                exists: false
            ),
            new FileTask(
                type: 'directory',
                path: $mainNode->workspacePath(),
                exists: true
            ),
            new ParallelTask(array_map(function (RepositoryNode $repositoryNode) {
                return new SequentialTask([
                    new GitRepositoryTask(
                        path: $repositoryNode->path(),
                        url: $repositoryNode->url(),
                    ),
                    $this->buildRepository($repositoryNode)
                ]);
            }, $mainNode->selectedRepositories()))
        ]);
    }

    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new NullTask();
    }
}
