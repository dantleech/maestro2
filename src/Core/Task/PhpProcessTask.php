<?php

namespace Maestro2\Core\Task;

use Closure;

class PhpProcessTask implements Task
{
    public function __construct(
        private array $args,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
    }

    /**
     * @return Closure(ProcessResult, Context)
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }

    public function args(): array
    {
        return $this->args;
    }
}
