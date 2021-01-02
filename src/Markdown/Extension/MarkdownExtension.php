<?php

namespace Maestro\Markdown\Extension;

use League\CommonMark\DocParser;
use League\CommonMark\Environment as LeagueEnvironment;
use Maestro\Core\Extension\CoreExtension;
use Maestro\Markdown\Task\MarkdownOrderHandler;
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
                $container->get(Environment::class),
                $container->get(DocParser::class)
            );
        }, [
            CoreExtension::TAG_TASK_HANDLER => []
        ]);

        $container->register(DocParser::class, function (Container $container) {
            $environment = LeagueEnvironment::createCommonMarkEnvironment();
            return new DocParser($environment);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
