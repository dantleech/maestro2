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
use Maestro2\Core\Task\ProcessTaskHandler;

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
                new ProcessTaskHandler(new Filesystem($this->workspace()->path()), $this->testRunner)
            ]),
            $this->reportPublisher,
            new Filesystem($this->workspace()->path())
        );
    }

    public function testExecutedGitCommit(): void
    {
        $this->workspace()->put('foo', '');
        $this->workspace()->put('bar', '');
        $this->testRunner->push(ProcessResult::ok([], $this->workspace()->path(), $this->workspace()->path()));
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $this->testRunner->push(ProcessResult::ok([], '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));

        self::assertEquals('git rev-parse --show-toplevel', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git add foo bar', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git commit -m Foobar', $this->testRunner->pop()->argsAsString());
    }

    public function testWarnsOnNonExistingPaths(): void
    {
        $this->workspace()->put('foo', '');

        $this->testRunner->push(ProcessResult::ok([], $this->workspace()->path(), $this->workspace()->path()));
        $this->testRunner->push(ProcessResult::ok([], '/'));
        $this->testRunner->push(ProcessResult::ok([], '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar', 'baz'],
            message: 'Foobar',
        ));

        self::assertEquals('git rev-parse --show-toplevel', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git add foo', $this->testRunner->pop()->argsAsString());
        self::assertEquals('git commit -m Foobar', $this->testRunner->pop()->argsAsString());
        self::assertCount(2, $this->reportPublisher->groups()->reports()->warns());
    }

    public function testTaskErrorIfNotAGitRepository(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not a git');
        $this->testRunner->push(ProcessResult::new([], '/', 128));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }

    public function testTaskErrorIfNotAGitRoot(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not the root');

        $this->testRunner->push(ProcessResult::ok([], '/', 'path/to/foo'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }
}
