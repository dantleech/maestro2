<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Manage a line in a file
 *
 * Replace or append a line to a file. For example:
 *
 * - Replace the Travis badge with the Github actions badge:
 * - Manage a `.gitignore` file
 *
 * ```php:task
 * new LineInFileTask(
 *     group: 'my-repository',
 *     path: 'README.md',
 *     regexp: '{Build Status.*travis}',
 *     line: sprintf('![CI](https://github.com/phpactor/%s/workflows/CI/badge.svg)', 'my-repository'),
 * );
 * ```
 */
class LineInFileTask implements Task, Stringable
{
    /**
     * @param string $path Workspace path to file to replace a line in
     * @param string $regexp Optional regular expression to match
     * @param string $line Line which should replace the matched line
     * @param bool $append Append line to the end of the file if it does not exist or if the regexp is not found
     */
    public function __construct(
        private string $path,
        private string $line,
        private ?string $regexp = null,
        private ?string $group = null,
        private bool $append = false
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function regexp(): ?string
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

    public function append(): bool
    {
        return $this->append;
    }
}
