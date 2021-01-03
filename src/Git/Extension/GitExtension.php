<?php

namespace Maestro\Git\Extension;

use Maestro\Composer\Task\ComposerHandler;
use Maestro\Core\Extension\CoreExtension;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Report\ReportManager;
use Maestro\Core\Vcs\RepositoryFactory;
use Maestro\Git\Task\GitSurveyHandler;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class GitExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(GitSurveyHandler::class, function (Container $container) {
            return new GitSurveyHandler(
                $container->get(RepositoryFactory::class)
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
