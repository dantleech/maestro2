<?php

namespace Maestro2\Core\Extension;

use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\TestEnqueuer;
use Maestro2\Core\Queue\TestQueue;
use Maestro2\Core\Task\HandlerFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(Queue::class, function (Container $container) {
            return new TestQueue($container);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
