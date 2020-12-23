<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\HttpClient\TestHttpClientInterceptor;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Process\TestProcessRunner;
use Maestro\Core\Report\ReportManager;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\HandlerFactory;
use Maestro\Core\Task\Task;
use Maestro\Tests\IntegrationTestCase;
use Phpactor\Container\Container;
use function Amp\Promise\wait;

abstract class HandlerTestCase extends IntegrationTestCase
{
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
        ], [
            $this->container->get(Filesystem::class),
            new TaskReportPublisher($this->container->get(ReportManager::class))
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

    protected function assertExpectedProcessesRan(): void
    {
        $remainingProcesses = array_map(function (ProcessResult $result) {
            return implode(' ', $result->cmd());
        }, $this->processRunner()->remainingExpectations());

        if ($remainingProcesses) {
            self::fail(sprintf(
                'The following processes were expected to be invoked but were not: %s',
                implode(', ', $remainingProcesses)
            ));
        }

        self::assertCount(0, $remainingProcesses);
    }

    protected function httpClient(): TestHttpClientInterceptor
    {
        return $this->container->get(TestHttpClientInterceptor::class);
    }
}
