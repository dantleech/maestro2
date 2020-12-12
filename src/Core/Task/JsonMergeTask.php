<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;

class JsonMergeTask implements Task, Stringable
{
    public function __construct(private string $path, private array $data = [], private ?Closure $filter = null)
    {
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
        return sprintf('treating JSON file "%s"', $this->path);
    }
}
