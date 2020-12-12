<?php

namespace Maestro2\Core\Report;

use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Task;
use Throwable;

interface TaskReportPublisher
{
    public function taskOk(Task $task, Context $context): void;

    public function taskFail(Task $task, Context $context, Throwable $error): void;
}
