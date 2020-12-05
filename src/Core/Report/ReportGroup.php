<?php

namespace Maestro2\Core\Report;

class ReportGroup
{
    public function __construct(private string $name, private array $reports)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<Report>
     */
    public function reports(): array
    {
        return $this->reports;
    }
}
