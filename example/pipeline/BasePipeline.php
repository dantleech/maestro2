<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Composer\Task\ComposerJsonFactTask;
use Maestro\Core\Fact\PhpFact;
use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Task\FactTask;
use Maestro\Core\Task\FileTask;
use Maestro\Core\Task\GitRepositoryTask;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\ParallelTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\Task;
use Maestro\Rector\Task\RectorInstallTask;

class BasePipeline implements Pipeline
{
    public function build(MainNode $mainNode): Task
    {
        return new SequentialTask([
            new PhpFact($mainNode->vars()->get('phpBin')),
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
