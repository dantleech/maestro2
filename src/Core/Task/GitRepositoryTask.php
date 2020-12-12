<?php

namespace Maestro2\Core\Task;
use Stringable;

class GitRepositoryTask implements Task, Stringable
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

    public function __toString(): string
    {
        return sprintf('Checking out git repository "%s"', $this->url());
    }
}
