<?php

namespace Maestro2\Core\Queue;

use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\TaskContext;
use Throwable;

interface Dequeuer
{
    public function dequeue(): ?TaskContext;

    public function resolve(TaskContext $task, ?Context $context, ?Throwable $error = null): void;
}
