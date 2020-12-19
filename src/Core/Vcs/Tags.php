<?php

namespace Maestro2\Core\Vcs;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Maestro2\Core\Vcs\Exception\VcsException;

class Tags implements IteratorAggregate, Countable
{
    /**
     * @param list<Tag> $tags
     */
    public function __construct(private array $tags)
    {
    }

    public function names(): array
    {
        return array_map(function (Tag $tag) {
            return $tag->name();
        }, $this->tags);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->tags);
    }

    public function mostRecent(): ?Tag
    {
        $tags = $this->sortTags($this->tags);
        $mostRecentKey = array_key_last($tags);

        if (null === $mostRecentKey) {
            return null;
        }

        return $tags[$mostRecentKey];
    }

    public function has(string $tagName): bool
    {
        foreach ($this->tags as $tag) {
            if ($tag->name() === $tagName) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->tags);
    }

    /**
     * @param list<Tag> $tags
     * @return list<Tag>
     */
    private function sortTags(array $tags): array
    {
        usort($tags, function (Tag $tag1, Tag $tag2) {
            return version_compare($tag1->name(), $tag2->name());
        });
        return $tags;
    }
}
