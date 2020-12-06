<?php

namespace Maestro2\Core\Task;

class JsonMergeTask implements Task
{
    public function __construct(private $path, private array $data)
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
}
