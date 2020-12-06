<?php

namespace Maestro2\Core\Config;

use DTL\Invoke\Invoke;
use Maestro2\Core\Config\Exception\ConfigError;

final class MainNode
{
    /**
     * @var array<RepositoryNode>
     */
    private array $repositories;
    private string $workspacePath;

    /**
     * @param array<array<string, mixed>> $repositories
     */
    public function __construct(string $workspacePath, array $repositories, private array $vars)
    {
        $this->repositories = array_map(
            fn (array $repository): RepositoryNode => Invoke::new(RepositoryNode::class, array_merge([
                'main' => $this,
            ], $repository)),
            $repositories
        );
        $this->workspacePath = $workspacePath;
    }

    /**
     * @param array<string, mixed> $main
     */
    public function fromArray(array $main): self
    {
        return Invoke::new(self::class, $main);
    }

    /**
     * @return array<RepositoryNode>
     */
    public function repositories(): array
    {
        return $this->repositories;
    }

    public function workspacePath(): string
    {
        return $this->workspacePath;
    }

    public function vars(): Vars
    {
        return new Vars($this->vars);
    }
}
