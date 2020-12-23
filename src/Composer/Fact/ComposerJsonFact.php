<?php

namespace Maestro\Composer\Fact;

use Maestro\Composer\ComposerPackages;
use Maestro\Core\Fact\Fact;

class ComposerJsonFact implements Fact
{
    public function __construct(private array $autoloadPaths, private ComposerPackages $packages)
    {
    }

    public function autoloadPaths(): array
    {
        return $this->autoloadPaths;
    }

    public function packages(): ComposerPackages
    {
        return $this->packages;
    }
}
