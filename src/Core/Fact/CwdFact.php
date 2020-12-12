<?php

namespace Maestro2\Core\Fact;

class CwdFact implements Fact
{
    public function __construct(private ?string $cwd)
    {
    }

    public function cwd(): ?string
    {
        return $this->cwd;
    }
}
