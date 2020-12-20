<?php

namespace Maestro2\Tests\Unit\Core\Task;

use Maestro2\Core\Task\GitSurveyTask;

class GitSurveyHandlerTest extends HandlerTestCase
{
    public function testPublishesTableRow(): void
    {
        $this->runTask(new GitSurveyTask());
        self::assertCount(1, $this->reportManager()->table()->rows());
    }
}
