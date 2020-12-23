<?php

namespace Maestro\Composer\Task;

use Amp\Promise;
use Amp\Success;
use Maestro\Composer\ComposerJson;
use Maestro\Composer\Fact\ComposerJsonFact;
use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\Task;

class ComposerJsonFactHandler implements Handler
{
    public function __construct()
    {
    }

    public function taskFqn(): string
    {
        return ComposerJsonFactTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        $composerJson = ComposerJson::fromProjectRoot(
            $context->service(Filesystem::class)->localPath()
        );

        return new Success($context->withFact(new ComposerJsonFact(
            autoloadPaths: $composerJson->autoloadPaths(),
            packages: $composerJson->packages(),
        )));
    }
}
