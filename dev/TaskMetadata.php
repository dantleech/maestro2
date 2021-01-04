<?php

namespace Maestro\Development;

final class TaskMetadata
{
    /**
     * @param TaskParameter[] $parameters
     * @param TaskExample[] $examples
     */
    public function __construct(
        private string $name,
        private string $shortDescription,
        private string $namespacedName,
        private string $documentation,
        private array $parameters,
        private array $examples
    )
    {
    }

    /**
     * @return TaskParameter[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    public function documentation(): string
    {
        return $this->documentation;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function namespacedName(): string
    {
        return $this->namespacedName;
    }

    public function shortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @return TaskExample[]
     */
    public function examples(): array
    {
        return $this->examples;
    }
}
