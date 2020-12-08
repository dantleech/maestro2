<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Task\GitCommitHandler;
use Maestro2\Core\Task\GitCommitTask;
use Maestro2\Core\Task\Handler;
use PHPUnit\Framework\TestCase;

class GitCommitHandlerTest extends HandlerTestCase
{
    private TestProcessRunner $testRunner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testRunner = new TestProcessRunner();
    }

    protected function createHandler(): Handler
    {
        return new GitCommitHandler(
            $this->testRunner
        );
    }

    public function testExecutedGitCommit(): void
    {
        $this->runTask(new GitCommitTask(
            paths: ['foo', 'bar'],
            message: 'Foobar',
            cwd: $this->workspace()->path()
        ));

        self::assertEquals([
            'git',
            'add',
            'foo',
            'bar',
        ], $this->testRunner->pop()->args());

        self::assertEquals([
            'git',
            'commit',
            '-m',
            'Foobar',
        ], $this->testRunner->pop()->args());
    }
}
