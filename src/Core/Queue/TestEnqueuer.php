<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;

final class TestEnqueuer implements Enqueuer
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
    public function enqueue(Task $task): Promise
    {
        return $this->handlerFactory->handlerFor($task)->run($task);
    }

    public function enqueueAll(array $tasks): void
    {
    }
}
