<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Psr\Log\LoggerInterface;
use function Amp\Promise\all;
use function Amp\Promise\first;
use function Amp\Promise\wrap;
use function Amp\call;

class Worker
{
    public function __construct(
        private Dequeuer $dequeuer,
        private LoggerInterface $logger,
        private HandlerFactory $handlerFactory,
        private int $concurrency = 5
    ) {
    }

    public function start(): Promise
    {
        return call(function () {
            $this->logger->info(sprintf('Worker starting with max concurrnecy %s', $this->concurrency));
            $promises = [];
            $id = 0;

            while ($task = $this->dequeuer->dequeue()) {
                $promises[--$id] = wrap($this->handlerFactory->handlerFor($task)->run($task), function () use (&$promises, $id) {
                    return $id;
                });

                if (count($promises) === $this->concurrency) {
                    unset($promises[yield first($promises)]);
                }
            }

            yield all($promises);
        });
    }
}
