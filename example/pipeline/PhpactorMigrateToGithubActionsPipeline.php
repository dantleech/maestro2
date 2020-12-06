<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class PhpactorMigrateToGithubActionsPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new FileTask(
                path: $repository->path('.travis.yml'),
                exists: false,
            ),
            (new GithubActionsPipeline())->build($repository),
            new CommandsTask(
                group: $repository->name(),
                commands: [
                    [ 'git',  'checkout',  '-b', 'githubactions' ],
                    [ 'git',  'rm',  '.travis.yml' ],
                    [ 'git',  'add',  '.github' ],
                    [ 'git',  'commit',  '-m', 'Add github actions' ],
                    [ 'git',  'push',  'origin', 'HEAD', '-f' ],
                ],
                cwd: $repository->path(),
                failFast: true
            ),
        ]);
    }
}
