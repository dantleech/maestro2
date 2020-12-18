<?php

namespace Maestro2\Core\Task;

use Stringable;

class ProcessesTask implements Task, Stringable
{
    /**
     * @param list<list<string>> $commands
     */
    public function __construct(
        private array $commands,
        private bool $failFast = false
    ) {
    }

    /**
     * @return list<list<string>>
     */
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
