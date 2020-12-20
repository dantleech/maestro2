<?php

namespace Maestro\Core\Extension;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Maestro\Core\HttpClient\TestHttpClientInterceptor;
use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Process\TestProcessRunner;
use Maestro\Core\Queue\Queue;
use Maestro\Core\Queue\TestQueue;
use Maestro\Core\Vcs\RepositoryFactory;
use Maestro\Core\Vcs\TestRepositoryFactory;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TestExtension implements Extension
{
    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(OutputInterface::class, function (Container $container) {
            return new BufferedOutput();
        });

        $container->register(Queue::class, function (Container $container) {
            return new TestQueue($container);
        });

        $container->register(ProcessRunner::class, function (Container $container) {
            return new TestProcessRunner();
        });

        $container->register(RepositoryFactory::class, function (Container $container) {
            return new TestRepositoryFactory();
        });

        $container->register(HttpClient::class, function (Container $container) {
            $builder = new HttpClientBuilder();
            $builder = $builder->intercept($container->get(TestHttpClientInterceptor::class));
            return $builder->build();
        });

        $container->register(TestHttpClientInterceptor::class, function (Container $container) {
            return new TestHttpClientInterceptor();
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
