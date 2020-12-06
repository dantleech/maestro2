<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\RepositoryPipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitRepositoryTask;
use Maestro2\Core\Task\JsonMergeTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TemplateTask;

class PhpactorRepositoryPipeline implements RepositoryPipeline
{
    public function build(RepositoryNode $repository): Task
    {
        return new SequentialTask(array_merge([
            new JsonMergeTask(
                path: $repository->path('composer.json'),
                data: [
                    'require' => [
                        'php' => '^7.3 || ^8.0',
                    ],
                    'require-dev' => array_merge([
                        'phpunit/phpunit' => '^8.0',
                    ], $repository->vars()->get('requireDev'))
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
