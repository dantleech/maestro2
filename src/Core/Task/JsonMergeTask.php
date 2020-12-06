<?php

namespace Maestro2\Core\Task;

use Closure;

class JsonMergeTask implements Task
{
    public function __construct(private $path, private array $data = [], private ?Closure $filter = null)
    {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function path()
    {
        return $this->path;
    }

    public function filter(): ?Closure
    {
        return $this->filter;
    }
}
