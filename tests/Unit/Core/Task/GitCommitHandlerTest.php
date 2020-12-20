<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Task\Exception\TaskError;
use Maestro2\Core\Task\GitCommitTask;

class GitCommitHandlerTest extends HandlerTestCase
{
    public function testExecutedGitCommit(): void
    {
        $this->filesystem()->putContents('foo', '');
        $this->filesystem()->putContents('bar', '');
        $this->processRunner()->expect(ProcessResult::ok('git rev-parse --show-toplevel', '/', stdOut: '/'));
        $this->processRunner()->expect(ProcessResult::ok('git add foo bar', '/'));
        $this->processRunner()->expect(ProcessResult::fail('git diff --staged --exit-code', '/', 1));
        $this->processRunner()->expect(ProcessResult::ok('git commit -m Foobar', '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));

        self::assertCount(0, $this->processRunner()->remainingExpectations());
    }

    public function testWarnsOnNonExistingPaths(): void
    {
        $this->filesystem()->putContents('foo', '');

        $this->processRunner()->expect(ProcessResult::ok('git rev-parse --show-toplevel', cwd: '/', stdOut: '/'));
        $this->processRunner()->expect(ProcessResult::ok('git add foo', '/'));
        $this->processRunner()->expect(ProcessResult::fail('git diff --staged --exit-code', '/', 1));
        $this->processRunner()->expect(ProcessResult::ok('git commit -m Foobar', '/'));
        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar', 'baz'],
            message: 'Foobar',
        ));

        self::assertCount(2, $this->reportManager()->reports()->warns()->matchingTitle('does not exist, ignoring'));
    }

    public function testTaskErrorIfNotAGitRepository(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not a git');
        $this->processRunner()->expect(ProcessResult::fail('git rev-parse --show-toplevel', cwd: '/', stdOut: '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }

    public function testTaskErrorIfNotAGitRoot(): void
    {
        $this->expectException(TaskError::class);
        $this->expectExceptionMessage('is not the root');

        $this->processRunner()->expect(ProcessResult::ok('git rev-parse --show-toplevel', cwd: '/asd', stdOut: '/'));

        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
        ));
    }
}
