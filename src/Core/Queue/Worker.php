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
use function Amp\delay;

class Worker
{
    private array $running = [];

    public function __construct(
        private Dequeuer $dequeuer,
        private LoggerInterface $logger,
        private HandlerFactory $handlerFactory,
        private int $concurrency = 10
    ) {
    }

    public function start(): Promise
    {
        return call(function () {
            $this->logger->info(sprintf('Worker starting with max concurrnecy %s', $this->concurrency));
            $promises = [];
            $id = 0;

            while (true) {
                $task = $this->dequeuer->dequeue();

                if (!$task && $this->running) {
                    yield delay(10);
                    continue;
                }

                if (!$task) {
                    break;
                }

                $promises[++$id] = call(function () use ($task, &$promises, $id) {

                    $this->running[$id] = $task;
                    $result = yield $this->handlerFactory->handlerFor($task)->run($task);
                    $this->dequeuer->resolve($task, $result);
                    unset($this->running[$id]);

                    return $id;
                });

                if (count($promises) === $this->concurrency) {
                    unset($promises[yield first($promises)]);
                }


                if (count($promises) === 0) {
                    break;
                }
            }

            return all($promises);
        });
    }
}
