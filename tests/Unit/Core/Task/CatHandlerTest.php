<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\CatHandler;
use Maestro2\Core\Task\CatTask;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Handler;

class CatHandlerTest extends HandlerTestCase
{
    private ReportManager $reportPublisher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportPublisher = new ReportManager();
    }

    protected function createHandler(): Handler
    {
        return new CatHandler(
            $this->filesystem(),
            $this->reportPublisher
        );
    }

    protected function defaultContext(): Context
    {
        return Context::fromFacts(
            new GroupFact('group')
        );
    }

    public function testReportsContentOfFileToReportManager(): void
    {
        $this->workspace()->put('path/foobar.txt', 'Hello');
        $this->runTask(new CatTask(
            path: 'path/foobar.txt'
        ));

        self::assertCount(1, $this->reportPublisher->groups()->reports()->infos());
    }
}
