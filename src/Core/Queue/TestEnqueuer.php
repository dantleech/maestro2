<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;

final class TestEnqueuer implements Enqueuer, Dequeuer
{
    public function __construct(private HandlerFactory $handlerFactory)
    {
    }

    public static function fromHandlers(array $handlers): self
    {
        return new self(new HandlerFactory($handlers));
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(Task $task, Context $context): Promise
    {
        return $this->handlerFactory->handlerFor($task)->run($task, $context);
    }

    public function dequeue(): ?Task
    {
        return null;
    }

    public function resolve(Task $task, $result): void
    {
    }
}
