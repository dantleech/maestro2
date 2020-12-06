<?php

namespace Maestro2\Core\Config;

use Maestro2\Core\Exception\RuntimeException;

class Vars
{
    public function __construct(private array $vars)
    {
    }

    public function get(string $key): mixed
    {
        if (!isset($this->vars[$key])) {
            throw new RuntimeException(sprintf(
                'Variable "%s" not found in scope, known variables "%s"',
                $key,
                implode('", "', array_keys($this->vars))
            ));
        }

        return $this->vars[$key];
    }

    public function toArray(): array
    {
        return $this->vars;
    }

    public function merge(Vars $vars): self
    {
        return new self(array_merge($this->vars, $vars->vars));
    }
}
