<?php

namespace Maestro2\Core\Task;

class FileTask implements Task
{
    public function __construct(
        private string $path,
        private string $type = 'file',
        private bool $exists = true,
        private int $mode = 0755,
        private ?string $content = null,
    ) {
    }

    public function mode(): int
    {
        return $this->mode;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function content(): ?string
    {
        return $this->content;
    }
}
