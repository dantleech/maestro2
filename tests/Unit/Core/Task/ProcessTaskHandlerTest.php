<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcess;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\ProcessTaskHandler;

class ProcessTaskHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact('/foobar')
        ]);
    }

    protected function createHandler(): Handler
    {
        return new ProcessTaskHandler(
            new Filesystem($this->workspace()->path()),
            $this->testRunner
        );
    }

    public function testRunsProcess(): void
    {
        $this->testRunner->push(ProcessResult::ok());
        $context = $this->runTask(new ProcessTask(
            args: ['foobar']
        ), Context::create([], [
            new CwdFact('foobar')
        ]));
        $process = $this->testRunner->pop();

        self::assertInstanceOf(TestProcess::class, $process);
        self::assertEquals(['foobar'], $process->args());
        self::assertEquals($this->workspace()->path('/foobar'), $process->cwd());
        self::assertInstanceOf(Context::class, $context);
    }

    public function testFailsWhenProcssFails(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->testRunner->push(ProcessResult::new(127));
        $context = $this->runTask(new ProcessTask(
            args: ['foobar']
        ));
    }

    public function testToleratesFailure(): void
    {
        $this->testRunner->push(ProcessResult::new(127));
        $context = $this->runTask(new ProcessTask(
            args: ['foobar'],
            allowFailure: true
        ));
        self::assertInstanceOf(Context::class, $context);
    }

    public function testAllowsModificationOfContextAfterProcessRuns(): void
    {
        $this->testRunner->push(ProcessResult::ok());
        $context = $this->runTask(new ProcessTask(
            args: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return $context->withVar('foo', 'bar');
            }
        ));
        self::assertInstanceOf(Context::class, $context);
        self::assertEquals('bar', $context->var('foo'));
    }

    public function testThrowsExceptionIfClosureDoesNotReturnContext(): void
    {
        $this->expectException(TaskError::class);
        $this->testRunner->push(ProcessResult::ok());
        $context = $this->runTask(new ProcessTask(
            args: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return 'foobar';
            }
        ));
    }
}
