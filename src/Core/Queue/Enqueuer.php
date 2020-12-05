<?php

namespace Maestro2\Core\Queue;

use Maestro2\Core\Task\Task;

interface Enqueuer
{
    public function enqueue(Task $task): void;

    public function enqueueAll(array $tasks): void;
}
