<?php

namespace Maestro2\Core\Queue;

use Amp\Promise;
use Maestro2\Core\Task\Context;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\TaskContext;
use Psr\Container\ContainerInterface;
use Throwable;
use Webmozart\Assert\Assert;

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
    public function resolve(TaskContext $task, ?Context $context, ?Throwable $error = null): void
    {
    }

    private function enqueuer(): TestEnqueuer
    {
        if ($this->enqueuer) {
            return $this->enqueuer;
        }

        $handlerFactory = $this->container->get(HandlerFactory::class);
        Assert::isInstanceOf($handlerFactory, HandlerFactory::class);
        $this->enqueuer = new TestEnqueuer($handlerFactory);

        return $this->enqueuer;
    }
}
