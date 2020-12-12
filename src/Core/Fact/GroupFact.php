<?php

namespace Maestro2\Core\Fact;

class GroupFact implements Fact
{
    public function __construct(private string $group) {
    }

    public function group(): string
    {
        return $this->group;
    }
}
