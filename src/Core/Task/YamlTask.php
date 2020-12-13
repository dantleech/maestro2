<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;

class YamlTask implements Task, Stringable
{
    public function __construct(
        private string $path,
        private array $data = [],
        private int $inline = 2,
        private ?Closure $filter = null
    ) {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function filter(): ?Closure
    {
        return $this->filter;
    }

    public function __toString(): string
    {
        return sprintf('Treated YAML file at "%s" (inline: %s)', $this->path, $this->inline);
    }

    public function inline(): int
    {
        return $this->inline;
    }
}
