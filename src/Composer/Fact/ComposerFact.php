<?php

namespace Maestro\Composer\Fact;

use Maestro\Composer\ComposerJson;
use Maestro\Composer\ComposerPackages;
use Maestro\Core\Fact\Fact;

class ComposerFact implements Fact
{
    private ComposerPackages $updated;

    public function __construct(private ComposerJson $json)
    {
        $this->updated = new ComposerPackages([]);
    }

    public function json(): ComposerJson
    {
        return $this->json;
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
