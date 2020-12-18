<?php

namespace Maestro2\Core\Task;

use Stringable;

class GitCommitTask implements Task, Stringable
{
    /**
     * @param list<string> $paths
     */
    public function __construct(
        private array $paths,
        private string $message,
        private ?string $group = null
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return list<string>
     */
    public function paths(): array
    {
        return $this->paths;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return sprintf('Git committing with "%s"', $this->message);
    }
}
