<?php

namespace Maestro\Composer\Fact;

use Maestro\Composer\ComposerPackages;
use Maestro\Core\Fact\Fact;

class ComposerJsonFact implements Fact
{
    private ComposerPackages $updated;

    public function __construct(private array $autoloadPaths, private ComposerPackages $packages)
    {
        $this->updated = new ComposerPackages([]);
    }

    public function autoloadPaths(): array
    {
        return $this->autoloadPaths;
    }

    public function packages(): ComposerPackages
    {
        return $this->packages;
    }

    public function withUpdated(ComposerPackages $composerPackages): self
    {
        $new = clone $this;
        $new->updated = $composerPackages;

        return $new;
    }

    public function updated(): ComposerPackages
    {
        return $this->updated;
    }
}
