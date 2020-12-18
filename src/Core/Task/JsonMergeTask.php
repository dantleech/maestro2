<?php

namespace Maestro2\Core\Task;

use Closure;
use Stringable;
use stdClass;

class JsonMergeTask implements Task, Stringable
{
    /**
     * @param Closure(stdClass):stdClass|null $filter
     */
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

    /**
     * @return Closure(stdClass $exitingData):stdClass|null
     */
    public function filter(): ?Closure
    {
        return $this->filter;
    }

    public function __toString(): string
    {
        return sprintf('treating JSON file "%s"', $this->path);
    }
}
