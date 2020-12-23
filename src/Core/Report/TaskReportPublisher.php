<?php

namespace Maestro\Core\Report;

use Maestro\Core\Task\Context;
use Maestro\Core\Task\Task;
use Stringable;
use Throwable;

class TaskReportPublisher
{
    public function __construct(private ReportManager $publisher, private string $group = 'workspace')
    {
    }

    public function withGroup(string $group): self
    {
        return new self($this->publisher, $group);
    }


    public function publish(Report $report): void
    {
        $this->publisher->publish($this->group, $report);
    }

    public function taskOk(Task $task, Context $context): void
    {
        // ignore boring non-stringable tasks
        if (!$task instanceof Stringable) {
            return;
        }

        $this->publisher->publish(
            $this->group,
            Report::ok($task->__toString())
        );
    }

    public function taskFail(Task $task, Context $context, Throwable $error): void
    {
        $this->publisher->publish(
            $this->group,
            Report::fail(
                $task instanceof Stringable ? $task->__toString() : $task::class,
                $error->getMessage()
            ),
        );
    }

    public function publishTableRow(array $row): void
    {
        $this->publisher->publishTableRow($this->group, $row);
    }
}
