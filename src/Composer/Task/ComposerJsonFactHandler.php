<?php

namespace Maestro2\Composer\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Composer\ComposerJson;
use Maestro2\Composer\Fact\ComposerJsonFact;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\Task;

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
