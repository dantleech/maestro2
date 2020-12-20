<?php

namespace Maestro\Core\Fact;

class PhpFact implements Fact
{
    public function __construct(private string $phpBin = PHP_BINARY)
    {
    }

    public function phpBin(): string
    {
        return $this->phpBin;
    }
}
