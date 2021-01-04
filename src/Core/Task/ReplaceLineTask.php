<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Replace a line in a file
 *
 * For example, replace the Travis badge with the Github actions badge:
 *
 * ```php
 * new ReplaceLineTask(
 *     group: $repository->name(),
 *     path: 'README.md',
 *     regexp: '{Build Status.*travis}',
 *     line: sprintf('![CI](https://github.com/phpactor/%s/workflows/CI/badge.svg)', $repository->name()),
 * ),
 * ```
 */
class ReplaceLineTask implements Task, Stringable
{
    /**
     * @param string $path Workspace path to file to replace a line in
     * @param string $regexp Regular expression to match
     * @param string $line Line which should replace the matched line
     */
    public function __construct(
        private string $path,
        private string $regexp,
        private string $line,
        private ?string $group = null
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function regexp(): string
    {
        return $this->regexp;
    }

    public function line(): string
    {
        return $this->line;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return 'Replacing line in file';
    }
}
