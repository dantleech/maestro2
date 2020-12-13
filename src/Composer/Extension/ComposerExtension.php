<?php

namespace Maestro2\Composer\Extension;

use Maestro2\Composer\Task\ComposerJsonFactHandler;
use Maestro2\Core\Extension\CoreExtension;
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
        $container->register(ComposerJsonFactHandler::class, function (Container $container) {
            return new ComposerJsonFactHandler();
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
