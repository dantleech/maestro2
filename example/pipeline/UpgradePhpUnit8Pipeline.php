<?php

namespace Maestro2\Examples\Pipeline;

use Maestro2\Composer\Task\ComposerJsonFactHandler;
use Maestro2\Composer\Task\ComposerJsonFactTask;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Pipeline\Pipeline;
use Maestro2\Core\Task\CommandsTask;
use Maestro2\Core\Task\ComposerTask;
use Maestro2\Core\Task\FileTask;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\NullTask;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\SequentialTask;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\YamlTask;
use Maestro2\Rector\Task\RectorComposerUpgradeTask;

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
                args: [
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
