<?php

namespace Maestro2\Core\Report;

interface ReportPublisher
{
    public function publish(string $group, Report $report): void;
}
