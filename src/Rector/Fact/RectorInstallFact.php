<?php

namespace Maestro2\Rector\Fact;

use Maestro2\Core\Fact\Fact;

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
