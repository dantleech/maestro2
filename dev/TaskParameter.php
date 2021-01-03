<?php

namespace Maestro\Development;

class TaskParameter
{
    public function __construct(private string $name, private string $type, private string $description)
    {
    }

    public function description(): string
    {
        return $this->description;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }
}
