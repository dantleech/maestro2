<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\Task;
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
    public function enqueue(Task $task): Promise
    {
        return $this->enqueuer()->enqueue($task);
    }

    public function dequeue(): ?Task
    {
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Task $task, $result): void
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
