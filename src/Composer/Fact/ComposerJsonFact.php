<?php

namespace Maestro\Composer\Fact;

use Maestro\Core\Fact\Fact;

class ComposerJsonFact implements Fact
{
    public function __construct(private array $autoloadPaths)
    {
    }

    public function autoloadPaths(): array
    {
        return $this->autoloadPaths;
    }
}
