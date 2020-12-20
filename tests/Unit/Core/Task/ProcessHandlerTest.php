<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Fact\CwdFact;
use Maestro\Core\Process\Exception\ProcessFailure;
use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Exception\TaskError;
use Maestro\Core\Task\ProcessTask;

class ProcessHandlerTest extends HandlerTestCase
{
    public function testRunsProcess(): void
    {
        $expected = ProcessResult::ok('foobar', '/');
        $this->processRunner()->expect($expected);
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
        $this->processRunner()->expect(ProcessResult::ok('foobar barfoo', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: 'foobar "barfoo"',
        ), Context::create([], [
            new CwdFact('foobar')
        ]));


        $process = $context->result();

        self::assertInstanceOf(ProcessResult::class, $process);
        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }


    public function testFailsWhenProcssFails(): void
    {
        $this->expectException(ProcessFailure::class);
        $this->processRunner()->expect(ProcessResult::fail('foobar', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar']
        ));
    }

    public function testToleratesFailure(): void
    {
        $this->processRunner()->expect(ProcessResult::fail('foobar', '/'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            allowFailure: true
        ));
        self::assertInstanceOf(Context::class, $context);
        self::assertCount(1, $this->reportManager()->groups()->reports()->warns());
    }

    public function testAllowsModificationOfContextAfterProcessRuns(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('foobar', '/'));
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
        $this->processRunner()->expect($expectedResult);

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
        $this->processRunner()->expect(ProcessResult::ok('foobar', '/', 'hello'));
        $context = $this->runTask(new ProcessTask(
            cmd: ['foobar'],
            after: function (ProcessResult $result, Context $context) {
                return 'foobar';
            }
        ));
    }
}
