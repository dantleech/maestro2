<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\Task;

interface Enqueuer
{
    /**
     * @return Promise<mixed>
     */
    public function enqueue(Task $task, Context $context): Promise;
}
