<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Process\Exception\ProcessFailure;
use Maestro2\Core\Process\ProcessResult;
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
            $this->filesystem(),
            $this->testRunner,
            $this->reportPublisher
        );
    }

    public function testRunsProcess(): void
    {
        $expected = ProcessResult::ok('foobar', '/');
        $this->testRunner->expect($expected);
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar']
        ), Context::create([], [
            new CwdFact('foobar')
        ]));

        $process = $context->result();

        self::assertSame($expected, $process);
    }

    public function testRunsProcessFromCommandString(): void
    {
        $this->testRunner->expect(ProcessResult::ok('foobar barfoo', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: 'foobar "barfoo"',
        ), Context::create([], [
            new CwdFact('foobar')
        ]));


        $process = $context->result();

        self::assertInstanceOf(ProcessResult::class, $process);
        self::assertCount(0, $this->testRunner->remainingExpectations());
    }


    public function testFailsWhenProcssFails(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->testRunner->expect(ProcessResult::fail('foobar', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar']
        ));
    }

    public function testToleratesFailure(): void
    {
        $this->testRunner->expect(ProcessResult::fail('foobar', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            allowFailure: true
        ));
        self::assertInstanceOf(Context::class, $context);
        self::assertCount(1, $this->reportPublisher->groups()->reports()->warns());
    }

    public function testAllowsModificationOfContextAfterProcessRuns(): void
    {
        $this->testRunner->expect(ProcessResult::ok('foobar', '/'));
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
        $expectedResult = ProcessResult::ok('foobar', '/', 'hello');
        $this->testRunner->expect($expectedResult);

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
        $this->testRunner->expect(ProcessResult::ok('foobar', '/', 'hello'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return 'foobar';
            }
        ));
    }
}
