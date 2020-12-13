<?php

namespace Maestro2\Composer\Task;

use Amp\Promise;
use Amp\Success;
use Maestro2\Composer\ComposerJson;
use Maestro2\Composer\Fact\ComposerJsonFact;
use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\Task;

class ComposerJsonFactHandler implements Handler
{
    public function taskFqn(): string
    {
        return ComposerJsonFactTask::class;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Task $task, Context $context): Promise
    {
        $cwd = $context->fact(CwdFact::class)->cwd();
        $composerJson = ComposerJson::fromProjectRoot($cwd);

        return new Success($context->withFact(new ComposerJsonFact(
            autoloadPaths: $composerJson->autoloadPaths()
        )));
    }
}
