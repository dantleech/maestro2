<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Maestro2\Tests\IntegrationTestCase;
use Phpactor\Container\Container;
use function Amp\Promise\wait;

abstract class HandlerTestCase extends IntegrationTestCase
{
    const EX_GROUP = 'group';

    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workspace()->reset();
        $this->workspace()->mkdir('templates');
        $this->container = parent::container();
    }

    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact('/'),
            new GroupFact(self::EX_GROUP),
        ]);
    }

    protected function runTask(Task $task, ?Context $context = null): Context
    {
        $context = $context ?: $this->defaultContext();

        return wait($this->container->get(HandlerFactory::class)->handlerFor($task)->run($task, $context)) ?: Context::create();
    }

    protected function reportManager(): ReportManager
    {
        return $this->container->get(ReportManager::class);
    }

    protected function filesystem(): Filesystem
    {
        return $this->container->get(Filesystem::class);
    }

    protected function processRunner(): TestProcessRunner
    {
        return $this->container->get(ProcessRunner::class);
    }
}
