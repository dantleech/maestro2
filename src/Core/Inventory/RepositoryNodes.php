<?php

namespace Maestro\Core\Inventory;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Maestro\Core\Exception\RuntimeException;

/**
 * @implements IteratorAggregate<RepositoryNode>
 */
class RepositoryNodes implements IteratorAggregate, Countable
{
    /**
     * @param list<RepositoryNode> $repositoryNodes
     */
    public function __construct(private array $repositoryNodes)
    {
    }

    public function getIterator()
    {
        return new ArrayIterator($this->repositoryNodes);
    }

    public function count(): int
    {
        return count($this->repositoryNodes);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_values(array_map(fn (RepositoryNode $r) => $r->name(), $this->repositoryNodes));
    }

    public function forTags(array $tags): self
    {
        if (empty($tags)) {
            return $this;
        }

        return new self(array_values(array_filter(
            $this->repositoryNodes,
            fn (RepositoryNode $r) => 0 !== count(array_intersect($r->tags(), $tags))
        )));
    }

    public function forNames(array $names): self
    {
        if (empty($names)) {
            return $this;
        }

        return new self(array_values(array_filter(
            $this->repositoryNodes,
            fn (RepositoryNode $r) => in_array($r->name(), $names)
        )));
    }

    public function get(string $name): RepositoryNode
    {
        foreach ($this->repositoryNodes as $repositoryNode) {
            if ($repositoryNode->name() === $name) {
                return $repositoryNode;
            }
        }

        throw new RuntimeException(sprintf(
            'Could not find repository with name "%s", known repositories: "%s"',
            $name,
            implode('", "', $this->names())
        ));
    }
}
