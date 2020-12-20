<?php

namespace Maestro\Core\Extension;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Maestro\Core\HttpClient\LoggingHttpClientInterceptor;
use Maestro\Core\Process\AmpProcessRunner;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Vcs\RepositoryFactory;
use Maestro\Git\GitRepositoryFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class RealExtension implements Extension
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(OutputInterface::class, function (Container $container) {
            return new ConsoleOutput();
        });

        $container->register(Queue::class, function (Container $container) {
            return new Queue();
        });

        $container->register(ProcessRunner::class, function (Container $container) {
            return new AmpProcessRunner(
                $container->get(LoggerInterface::class),
                $container->getParameter(CoreExtension::PARAM_CONCURRENCY)
            );
        });

        $container->register(RepositoryFactory::class, function (Container $container) {
            return new GitRepositoryFactory(
                $container->get(ProcessRunner::class),
                $container->get(LoggerInterface::class)
            );
        });

        $container->register(HttpClient::class, function (Container $container) {
            $builder = new HttpClientBuilder();
            $builder = $builder->intercept(new LoggingHttpClientInterceptor($container->get(LoggerInterface::class)));
            return $builder->build();
        });
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function configure(Resolver $schema)
    {
    }
}
