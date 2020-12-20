<?php

namespace Maestro\Core\Task;

class CatTask implements Task
{
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }
}
