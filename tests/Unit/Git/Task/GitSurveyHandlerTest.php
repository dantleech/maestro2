<?php

namespace Maestro\Tests\Unit\Git\Task;

use Maestro\Git\Task\GitSurveyTask;
use Maestro\Tests\Unit\Core\Task\HandlerTestCase;

class GitSurveyHandlerTest extends HandlerTestCase
{
    public function testPublishesTableRow(): void
    {
        $this->runTask(new GitSurveyTask());
        self::assertCount(1, $this->reportManager()->table()->rows());
    }
}
