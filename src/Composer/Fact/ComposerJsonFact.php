<?php

namespace Maestro2\Composer\Fact;

use Maestro2\Core\Fact\Fact;

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
