<?php

namespace Maestro\Composer\Extension;

use Maestro\Composer\Task\ComposerJsonFactHandler;
use Maestro\Core\Extension\CoreExtension;
use Maestro\Core\Filesystem\Filesystem;
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
            return new ComposerJsonFactHandler(
                $container->get(Filesystem::class)
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
