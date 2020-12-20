<?php

namespace Maestro\Core\Report\Publisher;

use Maestro\Core\Report\Report;
use Maestro\Core\Report\ReportPublisher;

class NullPublisher implements ReportPublisher
{
    public function publish(string $group, Report $report): void
    {
    }
}
