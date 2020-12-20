<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Task\GitSurveyTask;

class GitSurveyHandlerTest extends HandlerTestCase
{
    public function testPublishesTableRow(): void
    {
        $this->runTask(new GitSurveyTask());
        self::assertCount(1, $this->reportManager()->table()->rows());
    }
}
