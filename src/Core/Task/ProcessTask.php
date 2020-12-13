<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;

class ProcessTask implements Task, Stringable
{
    public function __construct(
        private array $args,
        private ?string $group = null,
        private ?string $cwd = null,
        private ?Closure $after = null,
        private bool $allowFailure = false
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

    public function __toString(): string
    {
        return sprintf('Running process: %s', implode(' ', array_map('escapeshellarg', $this->args)));
    }

    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }
}
