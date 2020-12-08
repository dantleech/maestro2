<?php

namespace Maestro2\Core\Process;

class TestProcess
{
    public function __construct(private array $args, private ?string $cwd = null)
    {
    }

    public function args(): array
    {
        return $this->args;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }

    public function argsAsString(): string
    {
        return implode(' ', $this->args);
    }
}
