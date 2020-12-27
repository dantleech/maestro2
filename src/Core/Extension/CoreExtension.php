<?php

namespace Maestro\Core\Extension;

use Amp\Http\Client\HttpClient;
use Maestro\Core\Extension\Context\DefaultContextFactory;
use Maestro\Core\Inventory\InventoryLoader;
use Maestro\Core\Exception\RuntimeException;
use Maestro\Core\Extension\Command\RunCommand;
use Maestro\Core\Extension\Logger\ConsoleLogger;
use Maestro\Core\Filesystem\Filesystem;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Queue\Worker;
use Maestro\Core\Report\ReportManager;
use Maestro\Core\Task\CatHandler;
use Maestro\Core\Task\HttpRequestHandler;
use Maestro\Core\Task\SetDirectoryHandler;
use Maestro\Core\Task\ClosureHandler;
use Maestro\Core\Task\ContextFactory;
use Maestro\Core\Task\DelegateHandler;
use Maestro\Core\Task\GitSurveyHandler;
use Maestro\Core\Task\JsonApiSurveyHandler;
use Maestro\Core\Task\PhpProcessHandler;
use Maestro\Core\Task\ConditionalHandler;
use Maestro\Core\Task\FactHandler;
use Maestro\Core\Task\FileHandler;
use Maestro\Core\Task\GitCommitHandler;
use Maestro\Core\Task\GitDiffHandler;
use Maestro\Core\Task\GitRepositoryHandler;
use Maestro\Core\Task\Handler;
use Maestro\Core\Task\HandlerFactory;
use Maestro\Core\Task\JsonMergeHandler;
use Maestro\Core\Task\NullTaskHandler;
use Maestro\Core\Task\ParallelHandler;
use Maestro\Core\Task\ProcessHandler;
use Maestro\Core\Task\ReplaceLineHandler;
use Maestro\Core\Task\SequentialHandler;
use Maestro\Core\Task\TemplateHandler;
use Maestro\Core\Task\YamlHandler;
use Maestro\Core\Vcs\RepositoryFactory;
use Maestro\Maestro;
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
use Webmozart\PathUtil\Path;

class CoreExtension implements Extension
{
    public const TAG_TASK_HANDLER = 'maestro.taskHandler';

    public const PARAM_TEMPLATE_PATH = 'core.templatePath';
    public const PARAM_WORKSPACE_PATH = 'core.workspacePath';
    public const PARAM_INVENTORY = 'core.inventory';
    public const PARAM_WORKING_DIRECTORY = 'core.workingDirectory';
    public const PARAM_CONCURRENCY = 'core.concurrency';
    public const PARAM_SECRET_PATH = 'core.secrets';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_SECRET_PATH => null,
            self::PARAM_INVENTORY => ['maestro-inventory.json'],
            self::PARAM_TEMPLATE_PATH => 'templates',
            self::PARAM_WORKSPACE_PATH => 'workspace',
            self::PARAM_WORKING_DIRECTORY => getcwd(),
            self::PARAM_CONCURRENCY => 4
        ]);
        $schema->setTypes([
            self::PARAM_INVENTORY => 'array',
            self::PARAM_TEMPLATE_PATH => 'string',
            self::PARAM_WORKSPACE_PATH => 'string',
            self::PARAM_CONCURRENCY => 'integer',
        ]);
        $schema->setDescriptions([
            self::PARAM_CONCURRENCY => 'Maximimum number of processes to run concurrently',
            self::PARAM_INVENTORY => 'Paths to inventory files, relative to working directory',
            self::PARAM_TEMPLATE_PATH => 'Base path for all templates',
            self::PARAM_WORKSPACE_PATH => 'Path to workspace',
            self::PARAM_WORKING_DIRECTORY => 'Working directory (set internally)',
        ]);
    }

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
                $container->get(Queue::class),
                $container->get(ContextFactory::class),
            );
        });

        $container->register(ContextFactory::class, function (Container $container) {
            return new DefaultContextFactory(
                $container->get(Filesystem::class),
                $container->get(ReportManager::class),
            );
        });

        $container->register(InventoryLoader::class, function (Container $container) {
            return new InventoryLoader(
                array_map(fn (string $path) => Path::join([
                    (string)$container->getParameter(self::PARAM_WORKING_DIRECTORY),
                    $path
                ]), $container->getParameter(self::PARAM_INVENTORY))
            );
        });

        $container->register(HandlerFactory::class, function (Container $container) {
            return new HandlerFactory(array_merge([
                new SequentialHandler(
                    $container->get(Queue::class)
                ),
                new ParallelHandler($container->get(Queue::class)),
                new GitRepositoryHandler($container->get(Queue::class)),
                new FileHandler($container->get(LoggerInterface::class)),
                new SetDirectoryHandler($container->get(Filesystem::class)),
                new ProcessHandler($container->get(ProcessRunner::class)),
                new PhpProcessHandler($container->get(Queue::class)),
                new NullTaskHandler(),
                new TemplateHandler(
                    $container->get(Environment::class)
                ),
                new JsonMergeHandler(),
                new YamlHandler(),
                new ReplaceLineHandler(),
                new GitDiffHandler($container->get(ProcessRunner::class)),
                new GitCommitHandler($container->get(Queue::class)),
                new FactHandler(),
                new ConditionalHandler($container->get(Queue::class)),
                new CatHandler(),
                new ClosureHandler(),
                new GitSurveyHandler($container->get(RepositoryFactory::class)),
                new HttpRequestHandler($container->get(HttpClient::class)),
                new JsonApiSurveyHandler($container->get(HttpClient::class)),
                new DelegateHandler($container->get(Queue::class)),
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
                        [$this->resolvePath($container, self::PARAM_TEMPLATE_PATH)]
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
                $this->resolvePath($container, self::PARAM_WORKSPACE_PATH)
            );
        });
    }

    private function resolvePath(Container $container, string $pathParameterName): string
    {
        $workingDirectory = (string)$container->getParameter(self::PARAM_WORKING_DIRECTORY);

        return Path::makeAbsolute($container->getParameter($pathParameterName), $workingDirectory);
    }
}
