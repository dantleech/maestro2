<?php

namespace Maestro2\Core\Task;

use Maestro2\Core\Fact\Fact;
use Maestro2\Core\Task\Exception\FactNotFound;

final class Context
{
    /**
     * @template T of Fact
     * @param array<string,mixed> $vars
     * @psalm-param array<class-string<T>,T> $facts
     */
    private function __construct(private array $vars = [], private array $facts = [])
    {
    }

    /**
     * @param array<string,mixed> $vars
     * @psalm-param array<int,Fact> $facts
     */
    public static function create(array $vars = [], array $facts = []): self
    {
        return new self($vars, array_combine(array_map('get_class', $facts), $facts));
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

    public function merge(?Context $context = null): self
    {
        if (null === $context) {
            return $this;
        }

        return new self(
            array_merge($this->vars, $context->vars),
            array_merge($this->facts, $context->facts),
        );
    }

    public function withVar(string $key, mixed $value): self
    {
        return (static function (array $vars) use ($key, $value): self {
            $vars[$key] = $value;
            return new self($vars);
        })($this->vars);
    }

    public function withFact(Fact $fact): self
    {
        return (function (array $facts) use ($fact): self {
            $facts[$fact::class] = $fact;
            return new self($this->vars, $facts);
        })($this->facts);
    }

    /**
     * @template F of Fact
     *
     * @psalm-param class-string<F> $factClass
     *
     * @return F
     */
    public function fact(string $factClass): Fact
    {
        if (!isset($this->facts[$factClass])) {
            throw new FactNotFound(sprintf(
                'Fact "%s" has not been set',
                $factClass
            ));
        }

        return $this->facts[$factClass];
    }
}
