<?php

namespace Maestro\Rector\Fact;

use Maestro\Core\Fact\Fact;

class RectorInstallFact implements Fact
{
    public function __construct(private string $binPath)
    {
    }

    public function binPath(): string
    {
        return $this->binPath;
    }
}
