<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Task\CatTask;

class CatHandlerTest extends HandlerTestCase
{
    public function testReportsContentOfFileToReportManager(): void
    {
        $this->workspace()->put('workspace/path/foobar.txt', 'Hello');

        $this->runTask(new CatTask(
            path: 'path/foobar.txt'
        ));

        self::assertCount(1, $this->reportManager()->reports()->infos());
    }
}
