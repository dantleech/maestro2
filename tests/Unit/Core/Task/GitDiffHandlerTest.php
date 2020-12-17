<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\ProcessResult;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\GitDiffHandler;
use Maestro2\Core\Task\GitDiffTask;
use Maestro2\Core\Task\Handler;

class GitDiffHandlerTest extends HandlerTestCase
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
            new GroupFact('group')
        );
    }

    protected function createHandler(): Handler
    {
        return new GitDiffHandler(
            new Filesystem($this->workspace()->path()),
            $this->testRunner,
            $this->reportPublisher
        );
    }

    public function testPublishesGitDiff(): void
    {
        $this->testRunner->push(ProcessResult::ok([], '/', 'diff'));
        $this->runTask(new GitDiffTask(), $this->defaultContext());

        self::assertCount(1, $this->reportPublisher->groups()->reports()->infos());
    }
}
