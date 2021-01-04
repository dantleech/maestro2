<?php

namespace Maestro\Core\Task;

use Maestro\Core\Report\TaskReportPublisher;

/**
 * Set the reporting / publishing group
 *
 * By default all reports are published to the `workspace` group.
 *
 * Use this task to specify a reporting group for subsequent tasks. Commonly 
 * you would use the name of the repository:
 * 
 * ```php
 * new SetReportingGroupTask('my-repo');
 * ```
 */
class SetReportingGroupTask implements DelegateTask
{
    public function __construct(private string $group)
    {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function task(): Task
    {
        return new ClosureTask(
            closure: function (Context $context) {
                return $context->withService(
                    $context->service(TaskReportPublisher::class)->withGroup($this->group())
                );
            }
        );
    }
}
