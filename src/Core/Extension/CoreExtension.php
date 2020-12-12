<?php

namespace Maestro2\Core\Extension;

use Maestro2\Core\Config\ConfigLoader;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Extension\Command\RunCommand;
use Maestro2\Core\Extension\Logger\ConsoleLogger;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Process\AmpProcessRunner;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\CommandsTaskHandler;
use Maestro2\Core\Task\ComposerHandler;
use Maestro2\Core\Task\FactHandler;
use Maestro2\Core\Task\FileHandler;
use Maestro2\Core\Task\GitCommitHandler;
use Maestro2\Core\Task\GitRepositoryHandler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Core\Task\NullTaskHandler;
use Maestro2\Core\Task\ParallelHandler;
use Maestro2\Core\Task\ProcessTaskHandler;
use Maestro2\Core\Task\ReplaceLineHandler;
use Maestro2\Core\Task\SequentialHandler;
use Maestro2\Core\Task\TemplateHandler;
use Maestro2\Core\Task\YamlHandler;
use Maestro2\Maestro;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class CoreExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(Maestro::class),
                $container->get(ReportManager::class)
            );
        });

        $container->register(Maestro::class, function (Container $container) {
            return new Maestro(
                $container->get(MainNode::class),
                $container->get(Worker::class),
                $container->get(Queue::class)
            );
        });

        $container->register(MainNode::class, function (Container $container) {
            return (function (ConfigLoader $loader) {
                return $loader->load();
            })($container->get(ConfigLoader::class));
        });

        $container->register(ConfigLoader::class, function (Container $container) {
            return new ConfigLoader([
                'maestro.json.dist',
                'maestro.json',
            ]);
        });

        $container->register(HandlerFactory::class, function (Container $container) {
            return new HandlerFactory([
                new SequentialHandler($container->get(Queue::class), $container->get(ReportManager::class)),
                new ParallelHandler($container->get(Queue::class), $container->get(ReportManager::class)),
                new FileHandler($container->get(LoggerInterface::class)),
                new GitRepositoryHandler($container->get(ProcessRunner::class), $container->get(WorkspacePathResolver::class)),
                new ProcessTaskHandler($container->get(ProcessRunner::class)),
                new CommandsTaskHandler($container->get(Queue::class)),
                new NullTaskHandler(),
                new TemplateHandler(
                    $container->get(WorkspacePathResolver::class),
                    $container->get(Environment::class),
                    $container->get(ArrayLoader::class),
                    $container->get(ReportManager::class)
                ),
                new JsonMergeHandler(),
                new YamlHandler(),
                new ReplaceLineHandler($container->get(ReportManager::class)),
                new ComposerHandler(
                    $container->get(Queue::class),
                    $container->get(ProcessRunner::class)
                ),
                new GitCommitHandler($container->get(ProcessRunner::class), $container->get(ReportManager::class)),
                new FactHandler(),
            ]);
        });

        $container->register(ProcessRunner::class, function (Container $container) {
            return new AmpProcessRunner($container->get(LoggerInterface::class));
        });

        $container->register(LoggerInterface::class, function (Container $container) {
            return new ConsoleLogger($container->get(OutputInterface::class));
        });

        $container->register(Worker::class, function (Container $container) {
            return new Worker(
                $container->get(Queue::class),
                $container->get(LoggerInterface::class),
                $container->get(HandlerFactory::class)
            );
        });

        $container->register(ReportManager::class, function (Container $container) {
            return new ReportManager();
        });

        $container->register(Environment::class, function (Container $container) {
            return new Environment(
                new ChainLoader([
                    $container->get(ArrayLoader::class),
                    new FilesystemLoader([ $container->getParameter('core.path.config') ])
                ])
            );
        });

        $container->register(ArrayLoader::class, function (Container $container) {
            return new ArrayLoader();
        });

        $container->register(WorkspacePathResolver::class, function (Container $container) {
            return new WorkspacePathResolver($this->getConfig($container)->workspacePath());
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setRequired([
            'core.path.config'
        ]);
    }

    private function getConfig(Container $container): MainNode
    {
        return $container->get(MainNode::class);
    }
}
