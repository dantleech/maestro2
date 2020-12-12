<?php

namespace Maestro2\Core\Task;

class CommandsTask implements Task
{
    public function __construct(
        private array $commands,
        private ?string $group = null,
        private bool $failFast = false,
        private ?string $cwd = null,
    ) {
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function cwd(): ?string
    {
        return $this->cwd;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function failFast(): bool
    {
        return $this->failFast;
    }
}
