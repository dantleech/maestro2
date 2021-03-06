<?php

namespace Maestro\Composer\Extension;

use Maestro\Composer\Task\ComposerHandler;
use Maestro\Core\Extension\CoreExtension;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Report\ReportManager;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class ComposerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(ComposerHandler::class, function (Container $container) {
            return new ComposerHandler(
                $container->get(Queue::class),
                $container->get(ReportManager::class)
            );
        }, [
            CoreExtension::TAG_TASK_HANDLER => []
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
