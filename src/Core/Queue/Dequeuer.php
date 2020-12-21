<?php

namespace Maestro\Core\Queue;

use Countable;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\TaskContext;
use Throwable;

interface Dequeuer extends Countable
{
    public function dequeue(): ?TaskContext;

    public function resolve(TaskContext $task, ?Context $context, ?Throwable $error = null): void;
}
