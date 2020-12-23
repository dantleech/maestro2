<?php

namespace Maestro\Tests\Unit\Core\Task;

use Maestro\Core\Report\Report;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\SetReportingGroupTask;

class SetReportingGroupTaskTest extends HandlerTestCase
{
    public function testSetsReportingGroup(): void
    {
        $context = $this->runTask(new SetReportingGroupTask('foobar'));
        $context->service(TaskReportPublisher::class)->publish(Report::ok('Foobar'));
        self::assertCount(1, $this->reportManager()->group('foobar')->reports()->oks());
    }
}
