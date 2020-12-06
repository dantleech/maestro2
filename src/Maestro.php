<?php

namespace Maestro2;

use Amp\Promise;
use Amp\Success;
use Maestro2\Core\Build\BuildFactory;
use Maestro2\Core\Config\ConfigLoader;
use Maestro2\Core\Config\MainNode;
use function Amp\call;

class Maestro
{
    public function __construct(private MainNode $mainNode, private BuildFactory $factory)
    {
    }

    /**
     * @param array<string> $targets
     */
    public function run(string $pipeline): Promise
    {
        return call(function () use ($pipeline) {
            $build = $this->factory->createBuild($pipeline);
            yield $build->start();
        });
    }
}
