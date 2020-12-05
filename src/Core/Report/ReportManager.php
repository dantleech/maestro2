<?php

namespace Maestro2\Core\Report;

class ReportManager implements ReportPublisher, ReportProvider
{
    private $reports = [];

    public function publish(string $group, Report $report): void
    {
        if (!isset($this->reports[$group])) {
            $this->reports[$group] = [];
        }

        $this->reports[$group][] = $report;
    }

    /**
     * {@inheritDoc}
     */
    public function groups(): array
    {
        return array_map(function (string $name, array $reports) {
            return new ReportGroup($name, $reports);
        }, array_keys($this->reports), $this->reports);
    }
}
