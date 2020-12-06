<?php

namespace Maestro2\Core\Extension;

use Amp\Process\Internal\ProcessRunner as AmpProcessRunner;
use Maestro2\Core\Build\BuildFactory;
use Maestro2\Core\Config\ConfigLoader;
use Maestro2\Core\Config\MainNode;
use Maestro2\Core\Extension\Command\ReplCommand;
use Maestro2\Core\Extension\Command\RunCommand;
use Maestro2\Core\Extension\Logger\ConsoleLogger;
use Maestro2\Core\Path\WorkspacePathResolver;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\CommandsTaskHandler;
use Maestro2\Core\Task\FileHandler;
use Maestro2\Core\Task\GitRepositoryHandler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Core\Task\NullTaskHandler;
use Maestro2\Core\Task\ProcessTaskHandler;
use Maestro2\Core\Task\ReplaceLineHandler;
use Maestro2\Core\Task\SequentialTaskHandler;
use Maestro2\Core\Task\TemplateHandler;
use Maestro2\Maestro;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Maestro2\Core\Task\ProcessTask;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class CoreExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(OutputInterface::class, function (Container $container) {
            return new ConsoleOutput();
        });

        $container->register(RunCommand::class, function (Container $container) {
            return new RunCommand(
                $container->get(Maestro::class),
                $container->get(ReportManager::class)
            );
        });

        $container->register(Maestro::class, function (Container $container) {
            return new Maestro(
                $container->get(BuildFactory::class)
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

        $container->register(BuildFactory::class, function (Container $container) {
            return new BuildFactory(
                $container->get(MainNode::class),
                $container->get(Queue::class),
                $container->get(Worker::class),
            );
        });
        
        $container->register(HandlerFactory::class, function (Container $container) {
            return new HandlerFactory([
                new SequentialTaskHandler($container->get(Queue::class)),
                new FileHandler($container->get(LoggerInterface::class)),
                new GitRepositoryHandler($container->get(ProcessRunner::class), $container->get(WorkspacePathResolver::class)),
                new ProcessTaskHandler($container->get(ProcessRunner::class), $container->get(ReportManager::class)),
                new CommandsTaskHandler($container->get(Queue::class)),
                new NullTaskHandler(),
                new TemplateHandler($container->get(WorkspacePathResolver::class), $container->get(Environment::class), $container->get(ReportManager::class)),
                new JsonMergeHandler(),
                new ReplaceLineHandler($container->get(ReportManager::class))
            ]);
        });

        $container->register(ProcessRunner::class, function (Container $container) {
            return new ProcessRunner($container->get(LoggerInterface::class));
        });

        $container->register(LoggerInterface::class, function (Container $container) {
            return new ConsoleLogger($container->get(OutputInterface::class));
        });

        $container->register(Queue::class, function (Container $container) {
            return new Queue();
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
            return new Environment(new FilesystemLoader([ $container->getParameter('core.path.config') ]));
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
