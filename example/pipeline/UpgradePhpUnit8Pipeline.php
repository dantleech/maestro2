<?php

namespace Maestro\Examples\Pipeline;

use Maestro\Composer\Task\ComposerJsonFactHandler;
use Maestro\Composer\Task\ComposerJsonFactTask;
use Maestro\Core\Inventory\MainNode;
use Maestro\Core\Inventory\RepositoryNode;
use Maestro\Core\Pipeline\Pipeline;
use Maestro\Composer\Task\ComposerTask;
use Maestro\Core\Task\FileTask;
use Maestro\Core\Task\GitCommitTask;
use Maestro\Core\Task\NullTask;
use Maestro\Core\Task\ProcessTask;
use Maestro\Core\Task\SequentialTask;
use Maestro\Core\Task\Task;
use Maestro\Core\Task\YamlTask;
use Maestro\Rector\Task\RectorComposerUpgradeTask;

class UpgradePhpUnit8Pipeline extends BasePipeline
{
    protected function buildRepository(RepositoryNode $repository): Task
    {
        return new SequentialTask([
            new RectorComposerUpgradeTask(
            ),
            new ComposerTask(
                dev: true,
                require: [
                    "phpunit/phpunit" => "^8.0"
                ],
                update: true,
            ),
            new ProcessTask(
                cmd: [
                    $repository->main()->php()->phpBin(),
                    'vendor/bin/phpunit'
                ],
            ),
            new GitCommitTask(
                paths: ['composer.json'],
                message: "Updated PHPUnit"
            )
        ]);
    }
}
