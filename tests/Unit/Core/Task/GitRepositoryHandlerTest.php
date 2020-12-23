<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Task\GitRepositoryTask;

class GitRepositoryHandlerTest extends HandlerTestCase
{
    public function testChecksOutNonExistingRepository(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('git clone http://example.com/git repo', '/'));
        $this->runTask(new GitRepositoryTask(
            url: 'http://example.com/git',
            path: 'repo',
        ));
        $this->assertExpectedProcessesRan();
    }

    public function testChecksOutBranch(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('git clone http://example.com/git repo --branch foobar', '/'));
        $this->runTask(new GitRepositoryTask(
            url: 'http://example.com/git',
            path: 'repo',
            branch: 'foobar',
        ));
        $this->assertExpectedProcessesRan();
    }

    public function testCleansExistingRepositoryByDefault(): void
    {
        $this->filesystem()->putContents('repo/.git/config', '');
        $this->processRunner()->expect(ProcessResult::ok('git clean -f -d', '/'));
        $this->runTask(new GitRepositoryTask(
            url: 'http://example.com/git',
            path: 'repo',
            clean: true
        ));
        $this->assertExpectedProcessesRan();
    }

    public function testNoCleanWhenOptionNotGiven(): void
    {
        $this->filesystem()->putContents('repo/.git/config', '');
        $this->runTask(new GitRepositoryTask(
            url: 'http://example.com/git',
            path: 'repo',
            clean: false
        ));
        $this->assertExpectedProcessesRan();
    }
}
