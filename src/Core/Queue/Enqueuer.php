<?php

namespace Maestro\Core\Queue;

use Amp\Promise;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\TaskContext;

interface Enqueuer
{
    /**
     * @return Promise<Context>
     */
    public function enqueue(TaskContext $task): Promise;
}
