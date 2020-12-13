<?php

namespace Maestro2\Core\Report;

use Maestro2\Core\Fact\GroupFact;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Task;
use RuntimeException;
use Stringable;
use Throwable;

class ReportManager implements ReportPublisher, ReportProvider, TaskReportPublisher
{
    /**
     * @var array<string, array<Report>>
     */
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
    public function groups(): ReportGroups
    {
        return new ReportGroups(array_map(function (string $name, array $reports) {
            return new ReportGroup($name, $reports);
        }, array_keys($this->reports), $this->reports));
    }

    public function taskOk(Task $task, Context $context): void
    {
        // ignore boring non-stringable tasks
        if (!$task instanceof Stringable) {
            return;
        }

        $this->publish(
            ($context->factOrNull(GroupFact::class)?->group()) ?: 'ungrouped',
            Report::ok($task instanceof Stringable ? $task->__toString() : $task::class)
        );
    }

    public function taskFail(Task $task, Context $context, Throwable $error): void
    {
        $this->publish(
            ($context->factOrNull(GroupFact::class)?->group()) ?: 'ungrouped',
            Report::fail(
                $task instanceof Stringable ? $task->__toString() : $task::class,
                $error->getMessage()
            ),
        );
    }
}
