<?php

namespace Maestro\Development;

class TaskExample
{
    public function __construct(private string $type, private string $exampe)
    {
    }

    public function exampe(): string
    {
        return $this->exampe;
    }

    public function type(): string
    {
        return $this->type;
    }
}
