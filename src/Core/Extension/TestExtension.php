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
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(OutputInterface::class, function (Container $container) {
            return new BufferedOutput();
        });

        $container->register(Queue::class, function (Container $container) {
            return new TestQueue($container);
        });
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function configure(Resolver $schema)
    {
    }
}
