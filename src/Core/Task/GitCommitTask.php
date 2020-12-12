<?php

namespace Maestro2\Core\Task;

class GitCommitTask implements Task
{
    public function __construct(
        private array $paths,
        private string $message,
        private string $cwd,
        private ?string $group = null
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }

    public function paths(): array
    {
        return $this->paths;
    }

    public function cwd(): string
    {
        return $this->cwd;
    }

    public function group(): ?string
    {
        return $this->group;
    }
}
