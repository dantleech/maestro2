<?php

namespace Maestro\Core\Task;

use Closure;
use Maestro\Core\Process\ProcessResult;
use Stringable;
use function Clue\Arguments\split;

class ProcessTask implements Task, Stringable
{
    /**
     * @var list<string>
     */
    private array $cmd;

    /**
     * @param list<string>|string $cmd
     * @param (Closure(ProcessResult, Context):Context)|null $after
     */
    public function __construct(
        array|string $cmd,
        private ?string $group = null,
        private ?Closure $after = null,
        private bool $allowFailure = false
    ) {
        if (is_string($cmd)) {
            $cmd = array_values(split($cmd));
        }
        $this->cmd = $cmd;
    }

    /**
     * @return list<string>
     */
    public function cmd(): array
    {
        return $this->cmd;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return sprintf(
            'Running process: %s',
            implode(' ', array_map('escapeshellarg', $this->cmd))
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
