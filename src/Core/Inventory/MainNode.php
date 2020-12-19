<?php

namespace Maestro2\Core\Inventory;

use DTL\Invoke\Invoke;
use Maestro2\Core\Exception\RuntimeException;

final class MainNode
{
    /**
     * @var array<RepositoryNode>
     */
    private array $repositories;

    /**
     * @param array<array<string, mixed>> $repositories
     * @param array<string> $selectedRepositories
     */
    public function __construct(
        array $repositories,
        private array $vars = [],
        private ?array $selectedRepositories = null
    ) {
        $this->repositories = (function (array $repositories) {
            return array_combine(array_map(
                fn (RepositoryNode $r) => $r->name(),
                $repositories
            ), $repositories);
        })(array_map(
            fn (array $repository): RepositoryNode => Invoke::new(RepositoryNode::class, array_merge([
                'main' => $this,
            ], $repository)),
            $repositories
        ));
    }

    /**
     * @param array<string, mixed> $main
     */
    public static function fromArray(array $main): self
    {
        return Invoke::new(self::class, $main);
    }

    /**
     * @return array<RepositoryNode>
     */
    public function selectedRepositories(): array
    {
        if (null === $this->selectedRepositories) {
            return $this->repositories;
        }
        return array_map(function (string $name) {
            if (!isset($this->repositories[$name])) {
                throw new RuntimeException(sprintf(
                    'Repository "%s" not known, known repositories "%s"',
                    $name,
                    implode('", "', array_keys($this->repositories))
                ));
            }

            return $this->repositories[$name];
        }, $this->selectedRepositories);
    }

    /**
     * @return array<RepositoryNode>
     */
    public function repositories(): array
    {
        return $this->repositories;
    }

    public function vars(): Vars
    {
        return new Vars($this->vars);
    }

    public function withSelectedRepos(?array $repos): self
    {
        $new = clone $this;
        $new->selectedRepositories = $repos;

        return $new;
    }
}
