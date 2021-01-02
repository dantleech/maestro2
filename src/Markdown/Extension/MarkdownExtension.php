<?php

namespace Maestro\Markdown\Extension;

use Maestro\Core\Extension\CoreExtension;
use Maestro\Markdown\Task\MarkdownSectionHandler;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Twig\Environment;

class MarkdownExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(MarkdownSectionHandler::class, function (Container $container) {
            return new MarkdownSectionHandler(
                $container->get(Environment::class)
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
