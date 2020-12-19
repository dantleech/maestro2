<?php

namespace Maestro2\Core\Report;

interface ReportTablePublisher
{
    /**
     * @param array<string,mixed> $data
     */
    public function publishTableRow(string $group, array $data): void;
}
