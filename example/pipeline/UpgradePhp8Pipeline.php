<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;

class UpgradePhp8Pipeline extends BasePipeline
{
    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new ComposerTask(
                path: $repository->path(),
                dev: true,
                require: [
                    "phpunit/phpunit" => "^8.0"
                ],
                update: true,
            ),
            new ProcessTask(
                args: [ $repository->main()->php()->phpBin(), 'vendor/bin/phpunit' ]
            )
        ]);
    }
}
