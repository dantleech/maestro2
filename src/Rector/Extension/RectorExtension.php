<?php

namespace Maestro2\Rector\Extension;

use Maestro2\Core\Extension\CoreExtension;
use Maestro2\Core\Queue\Queue;
use Maestro2\Rector\Task\RectorComposerUpgradeHandler;
use Maestro2\Rector\Task\RectorInstallHandler;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class RectorExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(RectorInstallHandler::class, function (Container $container) {
            return new RectorInstallHandler($container->get(Queue::class));
        }, [
            CoreExtension::TAG_TASK_HANDLER => []
        ]);
        $container->register(RectorComposerUpgradeHandler::class, function (Container $container) {
            return new RectorComposerUpgradeHandler($container->get(Queue::class));
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
