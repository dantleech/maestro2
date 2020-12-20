<?php

namespace Maestro\Core\Queue;

use Maestro\Core\Task\Context;
use Maestro\Core\Task\TaskContext;
use Throwable;

interface Dequeuer
{
    public function dequeue(): ?TaskContext;

    public function resolve(TaskContext $task, ?Context $context, ?Throwable $error = null): void;
}
