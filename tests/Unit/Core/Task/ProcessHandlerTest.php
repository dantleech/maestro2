<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcess;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessTask;
use Maestro2\Core\Task\ProcessHandler;

class ProcessHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;
    private ReportManager $reportPublisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
        $this->reportPublisher = new ReportManager();
    }

    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact('/foobar')
        ]);
    }

    protected function createHandler(): Handler
    {
        return new ProcessHandler(
            new Filesystem($this->workspace()->path()),
            $this->testRunner,
            $this->reportPublisher
        );
    }

    public function testRunsProcess(): void
    {
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar']
        ), Context::create([], [
            new CwdFact('foobar')
        ]));
        $process = $this->testRunner->shift();

        self::assertInstanceOf(TestProcess::class, $process);
        self::assertEquals(['foobar'], $process->cmd());
        self::assertEquals($this->workspace()->path('/foobar'), $process->cwd());
        self::assertInstanceOf(Context::class, $context);
    }

    public function testRunsProcessFromCommandString(): void
    {
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: 'foobar "barfoo"',
        ), Context::create([], [
            new CwdFact('foobar')
        ]));
        $process = $this->testRunner->shift();

        self::assertInstanceOf(TestProcess::class, $process);
        self::assertEquals(['foobar', 'barfoo'], $process->cmd());
        self::assertEquals($this->workspace()->path('/foobar'), $process->cwd());
        self::assertInstanceOf(Context::class, $context);
    }


    public function testFailsWhenProcssFails(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->testRunner->push(ProcessResult::new([], '/', 127));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar']
        ));
    }

    public function testToleratesFailure(): void
    {
        $this->testRunner->push(ProcessResult::new([], '/', 127));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            allowFailure: true
        ));
        self::assertInstanceOf(Context::class, $context);
        self::assertCount(1, $this->reportPublisher->groups()->reports()->warns());
    }

    public function testAllowsModificationOfContextAfterProcessRuns(): void
    {
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return $context->withVar('foo', 'bar');
            }
        ));
        self::assertInstanceOf(Context::class, $context);
        self::assertEquals('bar', $context->var('foo'));
    }

    public function testReturnsResult(): void
    {
        $expectedResult = ProcessResult::ok([], '/', 'hello');
        $this->testRunner->push($expectedResult);

        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return $context->withVar('foo', 'bar');
            }
        ));
        self::assertSame($expectedResult, $context->result());
    }

    public function testThrowsExceptionIfClosureDoesNotReturnContext(): void
    {
        $this->expectException(TaskError::class);
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return 'foobar';
            }
        ));
    }
}
