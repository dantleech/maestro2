<?php

namespace Maestro2\Core\Extension;

use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Process\TestProcessRunner;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\TestQueue;
use Maestro2\Core\Vcs\RepositoryFactory;
use Maestro2\Core\Vcs\TestRepositoryFactory;
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

        $container->register(ProcessRunner::class, function (Container $container) {
            return new TestProcessRunner();
        });

        $container->register(RepositoryFactory::class, function (Container $container) {
            return new TestRepositoryFactory();
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
