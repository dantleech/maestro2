<?php

namespace Maestro2\Core\Queue;

use Maestro2\Core\Task\TaskContext;

interface Dequeuer
{
    public function dequeue(): ?TaskContext;

    /**
     * @param mixed $result
     */
    public function resolve(TaskContext $task, $result): void;
}
