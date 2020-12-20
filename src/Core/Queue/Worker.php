<?php

namespace Maestro\Core\Queue;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Task\HandlerFactory;
use Maestro\Core\Task\TaskContext;
use Maestro\Core\Task\TaskUtil;
use Psr\Log\LoggerInterface;
use Stringable;
use Throwable;
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
    
    public function updateStatus(): void
    {
        $this->logger->debug(sprintf(
            'Running %s tasks: "%s", memory %sb',
            count($this->running),
            implode('", "', array_map(function (TaskContext $task) {
                return TaskUtil::describeShortName($task->task());
            }, array_filter(
                $this->running,
                fn (TaskContext $task) => $task->task() instanceof Stringable
            ))),
            number_format(memory_get_usage(true))
        ));
    }

    public function start(): Promise
    {
        return call(function () {
            $promises = [];
            $id = 0;

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
                    try {
                        $context = yield $this->handlerFactory->handlerFor($task->task())->run($task->task(), $task->context());
                        $this->dequeuer->resolve($task, $context);
                    } catch (Throwable $error) {
                        $this->dequeuer->resolve($task, null, $error);
                    }
                    unset($this->running[$id]);
                });
            }

            return new Success();
        });
    }
}
