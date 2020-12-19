<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Composer\Task\ComposerJsonFactTask;
use Maestro2\Core\Inventory\MainNode;
use Maestro2\Core\Inventory\RepositoryNode;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\FactTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ParallelTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Rector\Task\RectorInstallTask;

class BasePipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new GroupFact('workspace'),
            new FileTask(
                type: 'directory',
                path: 'build',
                exists: false
            ),
            new FileTask(
                type: 'directory',
                path: 'build',
                exists: true
            ),
            new ParallelTask(array_map(function (RepositoryNode $repositoryNode) {
                return new SequentialTask([
                    new GroupFact($repositoryNode->name()),
                    new CwdFact('build'),
                    new GitRepositoryTask(
                        url: $repositoryNode->url(),
                        path: $repositoryNode->name()
                    ),
                    new CwdFact('build/' . $repositoryNode->name()),
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
