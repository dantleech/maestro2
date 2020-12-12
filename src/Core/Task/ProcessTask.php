<?php

namespace Maestro2\Core\Task;

class ProcessTask implements Task
{
    public function __construct(
        private array $args,
        private ?string $group = null,
        private ?string $cwd = null
    ) {
    }

    public function args(): array
    {
        return $this->args;
    }

    public function cwd(): ?string
    {
        return $this->cwd;
    }

    public function group(): ?string
    {
        return $this->group;
    }
}
