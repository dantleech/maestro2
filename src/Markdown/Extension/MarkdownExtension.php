<?php

namespace Maestro\Markdown\Extension;

use Maestro\Composer\Task\ComposerHandler;
use Maestro\Core\Extension\CoreExtension;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Report\ReportManager;
use Maestro\Markdown\Task\MarkdownSectionHandler;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class MarkdownExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(MarkdownSectionHandler::class, function (Container $container) {
            return new MarkdownSectionHandler(
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

