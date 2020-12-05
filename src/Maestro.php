<?php

namespace Maestro2;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Build\BuildFactory;
use Maestro2\Core\Config\ConfigLoader;
use function Amp\call;

class Maestro
{
    public function __construct(private ConfigLoader $loader, private BuildFactory $factory)
    {
    }

    /**
     * @param array<string> $targets
     */
    public function run(array $targets): Promise
    {
        $config = $this->loader->load();

        return call(function () use ($config, $targets) {
            foreach ($targets as $target) {
                $build = $this->factory->createBuild($config, $target);
                yield $build->start();
            }
        });
    }
}
