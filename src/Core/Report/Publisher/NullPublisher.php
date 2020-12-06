<?php

namespace Maestro2\Core\Report\Publisher;

use Maestro2\Core\Report\Report;
use Maestro2\Core\Report\ReportPublisher;

class NullPublisher implements ReportPublisher
{
    public function publish(string $group, Report $report): void
    {
    }
}
