<?php

namespace Maestro2\Core\Vcs;

use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use Maestro\Library\Vcs\Exception\VcsException;

class Tags implements IteratorAggregate, Countable
{
    /**
     * @var Tag[]
     */
    private $tags = [];

    public function __construct(array $tags)
    {
        $tags = $this->sortTags($tags);
        foreach ($tags as $element) {
            $this->add($element);
        }
    }

    private function add(Tag $element): void
    {
        $this->tags[] = $element;
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

    public function mostRecent(): Tag
    {
        if (count($this->tags) === 0) {
            throw new VcsException(
                'Must have at least one tag in order to retrieve the most recent'
            );
        }

        return $this->tags[array_key_last($this->tags)];
    }

    public function has(string $tagName)
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

    private function sortTags(array $tags): array
    {
        usort($tags, function (Tag $tag1, Tag $tag2) {
            return version_compare($tag1->name(), $tag2->name());
        });
        return $tags;
    }
}
