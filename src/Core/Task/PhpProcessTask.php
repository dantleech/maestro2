<?php

namespace Maestro2\Core\Task;

use Closure;
use Maestro2\Core\Process\ProcessResult;

class PhpProcessTask implements Task
{
    /**
     * @param list<string> $cmd
     * @param (Closure(ProcessResult, Context): Context)|null $after
     */
    public function __construct(
        private array $cmd,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
    }

    /**
     * @return (Closure(ProcessResult, Context): Context)|null
     */
    public function after(): ?Closure
    {
        return $this->after;
    }

    public function allowFailure(): bool
    {
        return $this->allowFailure;
    }

    /**
     * @return list<string>
     */
    public function cmd(): array
    {
        return $this->cmd;
    }
}
