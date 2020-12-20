<?php

namespace Maestro\Core\Report;

interface ReportProvider
{
    /**
     * @return ReportGroups
     */
    public function groups(): ReportGroups;

    public function table(): Table;
}
