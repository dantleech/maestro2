<?php

namespace Maestro\Core\Report;

interface ReportPublisher
{
    public function publish(string $group, Report $report): void;
}
