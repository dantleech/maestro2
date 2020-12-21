<?php

namespace Maestro\Core\Queue;

use Amp\Promise;
use Amp\Success;
use Maestro\Core\Task\HandlerFactory;
use Maestro\Core\Task\TaskContext;
use Psr\Log\LoggerInterface;
use Stringable;
use Throwable;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\delay;

class Worker
{
    /**
     * @var array<int,TaskContext>
     */
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
            'Running %s/%s, memory %sb, last task: "%s"',
            count($this->running),
            $this->dequeuer->count() + count($this->running),
            number_format(memory_get_usage(true)),
            (function (array $tasks) {
                if (empty($this->running)) {
                    return '<no running tasks>';
                }

                $task = $this->running[array_key_last($this->running)];
                if ($task->task() instanceof Stringable) {
                    return $task->task()->__toString();
                }

                return get_class($task->task());
            })($this->running),
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
