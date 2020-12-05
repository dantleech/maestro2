<?php

namespace Maestro2\Core\Task;

class FileTask implements Task
{
    public function __construct(
        private string $path,
        private string $type = 'file',
        private bool $exists = true,
        private int $mode = 0777
    ) {
    }

    public function mode(): string
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
}
