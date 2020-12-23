<?php

namespace Maestro\Core\Task;

use Stringable;

class GitRepositoryTask implements Task, Stringable
{
    public function __construct(
        private string $url,
        private string $path,
        private bool $clean = true,
        private ?string $branch = null
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

    public function clean(): bool
    {
        return $this->clean;
    }

    public function branch(): ?string
    {
        return $this->branch;
    }
}
