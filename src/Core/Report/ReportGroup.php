<?php

namespace Maestro2\Core\Report;

class ReportGroup
{
    /**
     * @param array<array-key, Report> $reports
     */
    public function __construct(private string $name, private array $reports)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function reports(): Reports
    {
        return new Reports($this->reports);
    }
}
