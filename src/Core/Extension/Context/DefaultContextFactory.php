<?php

namespace Maestro\Core\Extension\Context;

use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Queue\Enqueuer;
use Maestro\Core\Queue\TaskRunner;
use Maestro\Core\Report\ReportManager;
use Maestro\Core\Report\TaskReportPublisher;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\ContextFactory;

class DefaultContextFactory implements ContextFactory
{
    public function __construct(
        private Filesystem $filesystem,
        private ReportManager $reportPublisher,
        private Enqueuer $enqueuer
    )
    {
    }

    public function createContext(): Context
    {
        return Context::create([], [], [
            $this->filesystem,
            new TaskReportPublisher($this->reportPublisher),
            new TaskRunner($this->enqueuer)
        ]);
    }
}
