<?php

namespace Maestro2\Core\Task;

class CommandsTask implements Task
{
    public function __construct(private array $commands, private ?string $cwd = null)
    {
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function cwd(): ?string
    {
        return $this->cwd;
    }
}
