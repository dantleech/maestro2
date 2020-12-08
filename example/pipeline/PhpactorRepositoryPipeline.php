<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Stage\RepositoryStage;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class PhpactorRepositoryPipeline implements RepositoryStage
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask(array_merge([
            new ComposerRectorTask(
                map: $repository->vars()->get('rector')
            ),
            new JsonMergeTask(
                path: $repository->path('composer.json'),
                data: [
                    'require' => $repository->vars()->get('composer.require'),
                    'require-dev' => $repository->vars()->get('composer.require-dev'),
                ]
            ),
        ], array_map(function (string $phpVersion) use ($repository) {
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
        }, [ 'php8.0' ])));
    }
}
