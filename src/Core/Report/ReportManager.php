<?php

namespace Maestro2\Core\Report;

use RuntimeException;

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

    public function group(string $name): ReportGroup
    {
        if (!isset($this->reports[$name])) {
            throw new RuntimeException(sprintf(
                'Report with group "%s" not know, known report groups: "%s"',
                $name,
                implode('", "', array_keys($this->reports))
            ));
        }

        return new ReportGroup($name, $this->reports[$name]);
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
