<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Task;

interface Enqueuer
{
    /**
     * @return Promise<mixed>
     */
    public function enqueue(Task $task): Promise;

    public function enqueueAll(array $tasks): void;
}
