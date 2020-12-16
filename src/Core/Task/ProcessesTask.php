<?php

namespace Maestro2\Core\Task;

use Stringable;

class ProcessesTask implements Task, Stringable
{
    public function __construct(
        private array $commands,
        private bool $failFast = false
    ) {
    }

    public function commands(): array
    {
        return $this->commands;
    }

    public function failFast(): bool
    {
        return $this->failFast;
    }

    public function __toString(): string
    {
        return 'Running commnads';
    }
}
