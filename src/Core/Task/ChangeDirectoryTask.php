<?php

namespace Maestro\Core\Task;

class ChangeDirectoryTask implements Task
{
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }
}
