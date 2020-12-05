<?php

namespace Maestro2\Core\Build;

use Amp\Promise;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use function Amp\Promise\all;
use function Amp\Promise\wait;
use function Amp\call;

class Build
{
    public function __construct(
        private HandlerFactory $handlerFactory,
        private array $tasks
    ) {
    }

    public function start(): Promise
    {
        return call(function (array $tasks) {
            $running = [];
            while ($task = array_shift($tasks)) {
                $running[] = $this->handlerFactory->handlerFor($task)->run($this->handlerFactory, $task);
            }

            yield all($running);
        }, $this->tasks);
    }
}
