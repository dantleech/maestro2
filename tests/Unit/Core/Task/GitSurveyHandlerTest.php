<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\CwdFact;
use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\GitSurveyHandler;
use Maestro2\Core\Task\GitSurveyTask;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Vcs\TestRepositoryFactory;
use PHPUnit\Framework\TestCase;

class GitSurveyHandlerTest extends HandlerTestCase
{
    private ReportManager $reportManager;

    protected function setUp(): void
    {
        $this->reportManager = new ReportManager();
    }

    protected function createHandler(): Handler
    {
        return new GitSurveyHandler(
            new TestRepositoryFactory(),
            $this->reportManager
        );
    }

    protected function defaultContext(): Context
    {
        return Context::create([], [
            new CwdFact($this->workspace()->path()),
            new GroupFact('group')
        ]);
    }

    public function testPublishesTableRow(): void
    {
        $this->runTask(new GitSurveyTask());
        self::assertCount(1, $this->reportManager->table()->rows());
    }
}
