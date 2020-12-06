<?php

namespace Maestro2\Core\Task;

class ReplaceLineTask implements Task
{
    public function __construct(private string $path, private string $regexp, private string $line, private string $group = 'replace-line') {
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

    public function group(): string
    {
        return $this->group;
    }
}
