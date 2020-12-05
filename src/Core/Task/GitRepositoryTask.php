<?php

namespace Maestro2\Core\Task;

class GitRepositoryTask implements Task
{
    public function __construct(
        private string $url,
        private string $path,
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): string
    {
        return $this->url;
    }
}
