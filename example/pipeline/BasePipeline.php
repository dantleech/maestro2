<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\FactTask;
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
            new FactTask(new GroupFact('workspace')),
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
                    new FactTask(new GroupFact($repositoryNode->name())),
                    new GitRepositoryTask(
                        path: $repositoryNode->path(),
                        url: $repositoryNode->url(),
                    ),
                    new FactTask(new CwdFact($repositoryNode->path())),
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
