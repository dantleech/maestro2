<?php

namespace Maestro\Development\Extension;

use Maestro\Development\Command\BuildCommand;
use Maestro\Development\TaskCompiler;
use Maestro\Development\TaskDocBuilder;
use Maestro\Development\TaskFinder;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

class DocExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container)
    {
        $container->register(
            BuildCommand::class,
            fn(Container $c) => new BuildCommand(
                $container->get(TaskCompiler::class)
            )
        );

        $container->register(
            TaskFinder::class,
            fn(Container $c) => new TaskFinder(
                __DIR__ . '/../../src',
            )
        );

        $container->register(
            TaskCompiler::class,
            fn(Container $c) => new TaskCompiler(
                $c->get(TaskFinder::class),
                $c->get(TaskDocBuilder::class),
                __DIR__ .'/../../docs/task',
            )
        );

        $container->register(
            TaskDocBuilder::class,
            fn(Container $c) => new TaskDocBuilder(
                $c->get(TaskFinder::class),
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }
}
