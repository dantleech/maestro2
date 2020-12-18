<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\TaskContext;

interface Enqueuer
{
    /**
     * @return Promise<Context>
     */
    public function enqueue(TaskContext $task): Promise;
}
