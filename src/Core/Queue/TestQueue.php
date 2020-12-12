<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
use Maestro2\Core\Task\TaskContext;
use Psr\Container\ContainerInterface;

class TestQueue implements Enqueuer, Dequeuer
{
    private ?TestEnqueuer $enqueuer;

    public function __construct(private ContainerInterface $container)
    {
        $this->enqueuer = null;
    }

    /**
     * {@inheritDoc}
     */
    public function enqueue(TaskContext $task): Promise
    {
        return $this->enqueuer()->enqueue($task);
    }

    public function dequeue(): ?TaskContext
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(TaskContext $task, $result): void
    {
    }

    private function enqueuer(): TestEnqueuer
    {
        if ($this->enqueuer) {
            return $this->enqueuer;
        }

        $this->enqueuer = new TestEnqueuer($this->container->get(HandlerFactory::class));

        return $this->enqueuer;
    }
}
