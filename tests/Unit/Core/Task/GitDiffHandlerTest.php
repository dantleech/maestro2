<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Process\ProcessResult;
use Maestro\Core\Task\GitDiffTask;

class GitDiffHandlerTest extends HandlerTestCase
{
    public function testPublishesGitDiff(): void
    {
        $this->processRunner()->expect(ProcessResult::ok('git diff', '/', 'diff'));
        $this->runTask(new GitDiffTask(), $this->defaultContext());

        self::assertCount(1, $this->reportManager()->groups()->reports()->infos());
    }
}
