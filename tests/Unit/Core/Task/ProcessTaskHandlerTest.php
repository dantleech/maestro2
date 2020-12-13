<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcess;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\ProcessTaskHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessTaskHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function createHandler(): Handler
    {
        return new ProcessTaskHandler($this->testRunner);
    }

    public function testRunsProcess(): void
    {
        $this->testRunner->push(ProcessResult::ok());
        $context = $this->runTask(new ProcessTask(
            cwd: '/foobar',
            args: ['foobar']
        ));
        $process = $this->testRunner->pop();

        self::assertInstanceOf(TestProcess::class, $process);
        self::assertEquals(['foobar'], $process->args());
        self::assertEquals('/foobar', $process->cwd());
        self::assertInstanceOf(Context::class, $context);
    }

    public function testFailsWhenProcssFails(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->testRunner->push(ProcessResult::new(127));
        $context = $this->runTask(new ProcessTask(
            cwd: '/foobar',
            args: ['foobar']
        ));
    }

    public function testToleratesFailure(): void
    {
        $this->testRunner->push(ProcessResult::new(127));
        $context = $this->runTask(new ProcessTask(
            cwd: '/foobar',
            args: ['foobar'],
            allowFailure: true
        ));
        self::assertInstanceOf(Context::class, $context);
    }
}
