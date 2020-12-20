<?php

namespace Maestro\Core\Report;

use Maestro\Core\Task\Context;
use Maestro\Core\Task\Task;
use Throwable;

interface TaskReportPublisher
{
    public function taskOk(Task $task, Context $context): void;

    public function taskFail(Task $task, Context $context, Throwable $error): void;
}
