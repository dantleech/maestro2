<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\TaskContext;

interface Enqueuer
{
    /**
     * @return Promise<mixed>
     */
    public function enqueue(TaskContext $task): Promise;
}
