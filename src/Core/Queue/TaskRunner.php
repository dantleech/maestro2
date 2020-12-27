<?php

namespace Maestro\Core\Queue;

use Amp\Promise;
use Maestro\Core\Task\Context;
use Maestro\Core\Task\TaskContext;

/**
 * Encapsulate the enqueue functionality in a dedicated class so it can be
 * exposed as a service
 */
final class TaskRunner
{
    public function __construct(private Enqueuer $enqueuer)
    {
    }

    /**
     * @return Promise<Context>
     */
    public function enqueue(TaskContext $task): Promise
    {
        return $this->enqueuer->enqueue($task);
    }
}
