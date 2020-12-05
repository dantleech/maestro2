<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Psr\Log\LoggerInterface;
use function Amp\Promise\all;
use function Amp\Promise\first;
use function Amp\Promise\wrap;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\delay;

class Worker
{
    private array $running = [];

    private bool $isRunning = true;

    public function __construct(
        private Dequeuer $dequeuer,
        private LoggerInterface $logger,
        private HandlerFactory $handlerFactory
    ) {
    }

    public function start(): Promise
    {
        return call(function () {
            $promises = [];
            $id = 0;

            asyncCall(function () {
                while ($this->isRunning) {
                    $this->logger->debug(sprintf('Running %s tasks', count($this->running)));
                    yield delay(1000);
                }
            });

            while (true) {
                $task = $this->dequeuer->dequeue();

                if (null === $task && $id > 0 && count($this->running) === 0) {
                    $this->isRunning = false;
                    break;
                }

                if (null === $task) {
                    yield delay(10);
                    continue;
                }

                $id++;
                asyncCall(function () use ($task, &$promises, $id) {
                    $this->running[$id] = $task;
                    $result = yield $this->handlerFactory->handlerFor($task)->run($task);
                    $this->dequeuer->resolve($task, $result);
                    unset($this->running[$id]);
                });
            }

            return new Success();
        });
    }
}
