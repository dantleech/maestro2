<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\HttpClient\TestHttpClientInterceptor;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Process\TestProcessRunner;
use Maestro\Core\Report\ReportManager;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\HandlerFactory;
use Maestro\Core\Task\Task;
use Maestro\Tests\IntegrationTestCase;
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

    protected function httpClient(): TestHttpClientInterceptor
    {
        return $this->container->get(TestHttpClientInterceptor::class);
    }
}
