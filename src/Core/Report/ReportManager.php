<?php

namespace Maestro\Core\Report;

use Maestro\Core\Fact\GroupFact;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\Task;
use RuntimeException;
use Stringable;
use Throwable;
use Maestro\Core\Report\Table;

class ReportManager implements ReportPublisher, ReportProvider, ReportTablePublisher
{
    /**
     * @var array<string, array<Report>>
     */
    private $reports = [];

    private Table $table;

    public function __construct()
    {
        $this->table = new Table();
    }

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

    public function reports(): Reports
    {
        return $this->groups()->reports();
    }

    /**
     * {@inheritDoc}
     */
    public function groups(): ReportGroups
    {
        return new ReportGroups(array_map(function (string $name, array $reports) {
            return new ReportGroup($name, $reports);
        }, array_keys($this->reports), $this->reports));
    }

    public function publishTableRow(string $group, array $data): void
    {
        $this->table->mergeRow($group, $data);
    }

    public function table(): Table
    {
        return $this->table;
    }
}
