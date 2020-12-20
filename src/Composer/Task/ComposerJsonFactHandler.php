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
    public function __construct(private Filesystem $filesystem)
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
            $this->filesystem->cd($context->fact(CwdFact::class)->cwd())->localPath()
        );

        return new Success($context->withFact(new ComposerJsonFact(
            autoloadPaths: $composerJson->autoloadPaths()
        )));
    }
}
