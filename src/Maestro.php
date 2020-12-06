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
    public function run(array $targets = []): Promise
    {
        return call(function () use ($targets) {
            foreach ($targets as $target) {
                $build = $this->factory->createBuild($this->mainNode);
                yield $build->start();
            }
        });
    }
}
