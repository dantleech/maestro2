<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class PhpactorMigrateToGithubActionsPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new FileTask(
                path: '.travis.yml',
                exists: false
            ),
            new TemplateTask(
                group: $repository->name(),
                template: 'example/pipeline/template/ci.yml.twig',
                target: $repository->path('.github/workflows/ci.yml'),
                overwrite: true,
                vars: [
                    'name' => $repository->name(),
                    'phpVersions' => [
                        '7.3',
                        '7.4',
                    ]
                ]
            ),
            new CommandsTask(
                group: $repository->name(),
                commands: [
                    [ 'git',  'checkout',  '-b', 'githubactions' ],
                    [ 'git',  'rm',  '.travis.yml' ],
                    [ 'git',  'add',  '.github' ],
                    [ 'git',  'commit',  '-m', 'Add github actions' ],
                    //[ 'git',  'push',  'origin', 'HEAD', '-f' ],
                ],
                cwd: $repository->path(),
                failFast: true
            ),
        ]);
    }

    private function buildCi(RepositoryNode $repository): Task
    {
        return new SequentialTask(array_map(function (string $phpVersion) use ($repository) {
            return new CommandsTask(
                group: $repository->name(),
                commands: [
                    [ $phpVersion, '/usr/local/bin/composer', 'install' ],
                    [ $phpVersion, 'vendor/bin/phpunit' ],
                    [ $phpVersion, 'vendor/bin/phpstan', 'analyse' ],
                ],
                cwd: $repository->path(),
                failFast: true
            );
        }, [ 'php7.3', 'php7.4', 'php8.0' ]));
    }
}
