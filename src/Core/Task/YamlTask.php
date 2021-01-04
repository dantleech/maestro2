<?php

namespace Maestro\Core\Task;

use Closure;
use Stringable;

/**
 * Manipulate a YAML file
 *
 * Merge data or filter a YAML file.
 *
 * Note that this command will reformat the YAML file.
 */
class YamlTask implements Task, Stringable
{
    /**
     * @param string $path Workspace path
     * @param array $data Merge this data into the YAML file
     * @param int $inline Start inlining the YAML at this level
     * @param ?Closure(array $data): array Filter the data with a Closure which accespts and returns an array.
     */
    public function __construct(
        private string $path,
        private array $data = [],
        private int $inline = 2,
        private ?Closure $filter = null
    ) {
    }

    public function data(): array
    {
        return $this->data;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function filter(): ?Closure
    {
        return $this->filter;
    }

    public function __toString(): string
    {
        return sprintf('Treated YAML file at "%s" (inline: %s)', $this->path, $this->inline);
    }

    public function inline(): int
    {
        return $this->inline;
    }
}
