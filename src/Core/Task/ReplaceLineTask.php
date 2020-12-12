<?php

namespace Maestro2\Core\Task;
use Stringable;

class ReplaceLineTask implements Task, Stringable
{
    public function __construct(
        private string $path,
        private string $regexp,
        private string $line,
        private ?string $group = null
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function regexp(): string
    {
        return $this->regexp;
    }

    public function line(): string
    {
        return $this->line;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return 'Replacing line in file';
    }
}
