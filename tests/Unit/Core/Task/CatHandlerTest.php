<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\CatTask;

class CatHandlerTest extends HandlerTestCase
{
    public function testReportsContentOfFileToReportManager(): void
    {
        $this->workspace()->put('workspace/path/foobar.txt', 'Hello');

        $this->runTask(new CatTask(
            path: 'path/foobar.txt'
        ));

        self::assertCount(1, $this->reportManager()->groups()->reports()->infos());
    }
}
