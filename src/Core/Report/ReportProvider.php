<?php

namespace Maestro2\Core\Report;

interface ReportProvider
{
    /**
     * @return array<ReportGroup>
     */
    public function groups(): array;
}
