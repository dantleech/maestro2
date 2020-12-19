<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\GitCommitHandler;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\ProcessHandler;

class GitCommitHandlerTest extends HandlerTestCase
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
        return Context::fromFacts(
            new GroupFact('git-commit'),
            new CwdFact('/')
        );
    }

    protected function createHandler(): Handler
    {
        return new GitCommitHandler(
            TestEnqueuer::fromHandlers([
                new ProcessHandler(new Filesystem($this->workspace()->path()), $this->testRunner)
            ]),
            $this->reportPublisher,
            new Filesystem($this->workspace()->path())
        );
    }

    public function testExecutedGitCommit(): void
    {
        $this->workspace()->put('foo', '');
        $this->workspace()->put('bar', '');
        $this->testRunner->expect(ProcessResult::ok('git rev-parse --show-toplevel', '/', stdOut: '/'));
        $this->testRunner->expect(ProcessResult::ok('git add foo bar', '/'));
        $this->testRunner->expect(ProcessResult::fail('git diff --staged --exit-code', '/', 1));
        $this->testRunner->expect(ProcessResult::ok('git commit -m Foobar', '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));

        self::assertCount(0, $this->testRunner->remainingExpectations());
    }

    public function testWarnsOnNonExistingPaths(): void
    {
        $this->workspace()->put('foo', '');

        $this->testRunner->expect(ProcessResult::ok('git rev-parse --show-toplevel', cwd: '/', stdOut: '/'));
        $this->testRunner->expect(ProcessResult::ok('git add foo', '/'));
        $this->testRunner->expect(ProcessResult::fail('git diff --staged --exit-code', '/', 1));
        $this->testRunner->expect(ProcessResult::ok('git commit -m Foobar', '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar', 'baz'],
            message: 'Foobar',
        ));

        self::assertCount(2, $this->reportPublisher->groups()->reports()->warns());
    }

    public function testTaskErrorIfNotAGitRepository(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not a git');
        $this->testRunner->expect(ProcessResult::fail('git rev-parse --show-toplevel', cwd: '/', stdOut: '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }

    public function testTaskErrorIfNotAGitRoot(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not the root');

        $this->testRunner->expect(ProcessResult::ok('git rev-parse --show-toplevel', cwd: '/asd', stdOut: '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }
}
