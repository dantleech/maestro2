<?php

namespace Maestro2\Core\Task;

final class Context
{
    public function __construct(private array $vars = [])
    {
    }
    public static function create(): self
    {
        return new self();
    }

    public function var(string $name, mixed $default = null): mixed
    {
        if (!array_key_exists($name, $this->vars)) {
            return $default;
        }

        return $this->vars[$name];
    }

    public function vars(): array
    {
        return $this->vars;
    }

    public function merge(array $context): self
    {
        return new self(array_merge($this->vars, $context));
    }
}
