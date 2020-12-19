<?php

namespace Maestro2\Core\Extension;

use Maestro2\Core\Inventory\InventoryLoader;
use Maestro2\Core\Exception\RuntimeException;
use Maestro2\Core\Extension\Command\RunCommand;
use Maestro2\Core\Extension\Logger\ConsoleLogger;
use Maestro2\Core\Filesystem\Filesystem;
use Maestro2\Core\Process\AmpProcessRunner;
use Maestro2\Core\Process\ProcessRunner;
use Maestro2\Core\Queue\Queue;
use Maestro2\Core\Queue\Worker;
use Maestro2\Core\Report\ReportManager;
use Maestro2\Core\Task\CatHandler;
use Maestro2\Core\Task\GitSurveyHandler;
use Maestro2\Core\Task\PhpProcessHandler;
use Maestro2\Core\Task\ComposerHandler;
use Maestro2\Core\Task\ConditionalHandler;
use Maestro2\Core\Task\FactHandler;
use Maestro2\Core\Task\FileHandler;
use Maestro2\Core\Task\GitCommitHandler;
use Maestro2\Core\Task\GitDiffHandler;
use Maestro2\Core\Task\GitRepositoryHandler;
use Maestro2\Core\Task\Handler;
use Maestro2\Core\Task\HandlerFactory;
use Maestro2\Core\Task\JsonMergeHandler;
use Maestro2\Core\Task\NullTaskHandler;
use Maestro2\Core\Task\ParallelHandler;
use Maestro2\Core\Task\ProcessHandler;
use Maestro2\Core\Task\ReplaceLineHandler;
use Maestro2\Core\Task\SequentialHandler;
use Maestro2\Core\Task\TemplateHandler;
use Maestro2\Core\Task\YamlHandler;
use Maestro2\Core\Vcs\RepositoryFactory;
use Maestro2\Git\GitRepositoryFactory;
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
    public const TAG_TASK_HANDLER = 'maestro.taskHandler';

    public const PARAM_TEMPLATE_PATHS = 'core.templatePaths';
    public const PARAM_INVENTORY = 'core.inventory';
    public const PARAM_WORKSPACE_PATH = 'core.workspacePath';

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
                $container->get(InventoryLoader::class),
                $container->get(Worker::class),
                $container->get(Queue::class)
            );
        });

        $container->register(InventoryLoader::class, function (Container $container) {
            return new InventoryLoader($container->getParameter(self::PARAM_INVENTORY));
        });

        $container->register(HandlerFactory::class, function (Container $container) {
            return new HandlerFactory(array_merge([
                new SequentialHandler(
                    $container->get(Queue::class),
                    $container->get(ReportManager::class)
                ),
                new ParallelHandler($container->get(Queue::class), $container->get(ReportManager::class)),
                new FileHandler($container->get(Filesystem::class), $container->get(LoggerInterface::class)),
                new GitRepositoryHandler($container->get(Queue::class)),
                new ProcessHandler($container->get(Filesystem::class), $container->get(ProcessRunner::class)),
                new PhpProcessHandler($container->get(Queue::class)),
                new NullTaskHandler(),
                new TemplateHandler(
                    $container->get(Filesystem::class),
                    $container->get(Environment::class),
                    $container->get(ArrayLoader::class),
                    $container->get(ReportManager::class)
                ),
                new JsonMergeHandler($container->get(Filesystem::class), ),
                new YamlHandler($container->get(Filesystem::class), ),
                new ReplaceLineHandler($container->get(Filesystem::class), $container->get(ReportManager::class)),
                new ComposerHandler(
                    $container->get(Filesystem::class),
                    $container->get(Queue::class)
                ),
                new GitDiffHandler($container->get(Filesystem::class), $container->get(ProcessRunner::class), $container->get(ReportManager::class)),
                new GitCommitHandler($container->get(Queue::class), $container->get(ReportManager::class), $container->get(Filesystem::class)),
                new FactHandler(),
                new ConditionalHandler($container->get(Queue::class), $container->get(ReportManager::class)),
                new CatHandler($container->get(Filesystem::class), $container->get(ReportManager::class)),
                new GitSurveyHandler($container->get(Filesystem::class), $container->get(RepositoryFactory::class), $container->get(ReportManager::class)),
            ], (static function (array $taggedServices) use ($container) {
                return array_map(static function ($serviceId) use ($container): Handler {
                    $handler = $container->get($serviceId);
                    if (!$handler instanceof Handler) {
                        throw new RuntimeException(sprintf(
                            'Expected service "%s" to be a handler but it\'s not',
                            $serviceId
                        ));
                    }

                    return $handler;
                }, array_keys($taggedServices));
            })($container->getServiceIdsForTag(self::TAG_TASK_HANDLER))));
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
                    new FilesystemLoader(
                        $container->getParameter(self::PARAM_TEMPLATE_PATHS)
                    )
                ]),
                [
                    'strict_variables' => true
                ]
            );
        });

        $container->register(ArrayLoader::class, function (Container $container) {
            return new ArrayLoader();
        });

        $container->register(Filesystem::class, function (Container $container) {
            return new Filesystem(
                $container->getParameter(self::PARAM_WORKSPACE_PATH)
            );
        });

        $container->register(RepositoryFactory::class, function (Container $container) {
            return new GitRepositoryFactory(
                $container->get(ProcessRunner::class),
                $container->get(LoggerInterface::class)
            );
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_INVENTORY => 'maestro-inventory.json',
            self::PARAM_TEMPLATE_PATHS => [
                'templates',
            ],
            self::PARAM_WORKSPACE_PATH => 'workspace',
        ]);
    }
}
