<?php

namespace Maestro2\Core\Task;

use Closure;
use Maestro2\Core\Process\ProcessResult;
use Stringable;

class ProcessTask implements Task, Stringable
{
    /**
     * @param list<string> $args
     * @param (Closure(ProcessResult, Context):Context)|null $after
     */
    public function __construct(
        private array $args,
        private ?string $group = null,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
    }

    /**
     * @return list<string>
     */
    public function args(): array
    {
        return $this->args;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return sprintf(
            'Running process: %s',
            implode(' ', array_map('escapeshellarg', $this->args))
        );
    }

    /**
     * @return Closure(ProcessResult, Context): Context
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }
}
