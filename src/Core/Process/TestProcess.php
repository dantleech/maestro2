<?php

namespace Maestro2\Core\Process;

class TestProcess
{
    public function __construct(private array $cmd, private ?string $cwd = null)
    {
    }

    public function cmd(): array
    {
        return $this->cmd;
    }

    public function cwd(): ?string
    {
        return $this->cwd;
    }

    public function cmdAsString(): string
    {
        return implode(' ', $this->cmd);
    }
}
