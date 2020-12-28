<?php

namespace Maestro\Core\Task;

use Maestro\Core\Fact\Fact;
use Maestro\Core\Task\Exception\FactNotFound;
use Maestro\Core\Task\Exception\ResultNotSet;
use Maestro\Core\Task\Exception\ServiceNotFound;

/**
 * @template R
 */
final class Context
{
    /**
     * @template T of Fact
     * @template F
     * @param array<string,mixed> $vars
     * @psalm-param array<class-string<T>,T> $facts
     * @psalm-param array<class-string<F>,F> $services
     * @param R $result
     */
    private function __construct(private array $vars, private array $facts, private array $services, private mixed $result = null)
    {
    }

    /**
     * @param array<string,mixed> $vars
     * @psalm-param list<Fact> $facts
     * @psalm-param list<object> $services
     */
    public static function create(array $vars = [], array $facts = [], array $services = []): self
    {
        return new self(
            $vars,
            array_combine(array_map('get_class', $facts), $facts),
            array_combine(array_map('get_class', $services), $services),
        );
    }

    /**
     * @return R
     */
    public function result(): mixed
    {
        if (null === $this->result) {
            throw new ResultNotSet(
                'No result has been set in context'
            );
        }

        return $this->result;
    }

    /**
     * @return R|null
     */
    public function resultOrNull(): mixed
    {
        return $this->result;
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
            array_merge($this->services, $context->services),
            $context->result
        );
    }

    public function withVar(string $key, mixed $value): self
    {
        return (function (array $vars) use ($key, $value): self {
            $vars[$key] = $value;
            return new self($vars, $this->facts, $this->services);
        })($this->vars);
    }

    public function withFact(Fact $fact): self
    {
        return (function (array $facts) use ($fact): self {
            $facts[$fact::class] = $fact;
            return new self($this->vars, $facts, $this->services);
        })($this->facts);
    }

    public function withService(object $service): self
    {
        return (function (array $services) use ($service): self {
            $services[$service::class] = $service;
            return new self($this->vars, $this->facts, $services);
        })($this->services);
    }

    /**
     * @param R $result
     */
    public function withResult(mixed $result): self
    {
        return new self($this->vars, $this->facts, $this->services, $result);
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
        return $this->factOrNull($factClass) ?: throw new FactNotFound(sprintf(
            'Fact "%s" has not been set',
            $factClass
        ));
    }

    /**
     * @template S of object
     *
     * @psalm-param class-string<S> $serviceClass
     *
     * @return S
     */
    public function service(string $serviceClass): object
    {
        if (!isset($this->services[$serviceClass])) {
            throw new ServiceNotFound(sprintf(
                'Service "%s" has not been registered',
                $serviceClass
            ));
        }

        return $this->services[$serviceClass];
    }

    /**
     * @template F of Fact
     *
     * @psalm-param class-string<F> $factClass
     *
     * @return ?F
     */
    public function factOrNull(string $factClass): ?Fact
    {
        if (!isset($this->facts[$factClass])) {
            return null;
        }

        return $this->facts[$factClass];
    }


    public static function fromFacts(Fact ...$phpFacts): self
    {
        return self::create([], $phpFacts);
    }
}
