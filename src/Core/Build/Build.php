<?php

namespace Maestro2\Core\Build;

use Amp\Promise;
use Maestro2\Core\Queue\Dequeuer;
use Maestro2\Core\Queue\Enqueuer;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use function Amp\Promise\all;
use function Amp\Promise\wait;
use function Amp\call;

class Build
{
    public function __construct(
        private Enqueuer $queue,
        private array $tasks,
        private Worker $worker
    ) {
    }

    public function start(): Promise
    {
        return call(function (array $tasks) {
            foreach ($tasks as $task) {
                $this->queue->enqueue($task);
            }
            yield $this->worker->start();
        }, $this->tasks);
    }
}
