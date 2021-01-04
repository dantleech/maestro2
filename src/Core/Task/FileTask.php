<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Manage a file
 *
 * This task can be used to manage the state of a file.
 *
 * Ensure file exists:
 *
 * ```php
 * new FileTask(
 *     path: "README.md",
 *     contents: "Hello World"
 * )
 * ```
 *
 * Remove a file:
 *
 * ```php:task
 * new FileTask(
 *     path: "README.md",
 *     exists: false
 * )
 * ```
 *
 * Create a directory
 *
 * ```php:task
 * new FileTask(
 *     path: "myDirectory",
 *     type: "directory"
 * )
 * ```
 */
class FileTask implements Task, Stringable
{
    public function __construct(
        private string $path,
        private string $type = 'file',
        private bool $exists = true,
        private int $mode = 0755,
        private ?string $content = null,
    ) {
    }

    public function mode(): int
    {
        return $this->mode;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function __toString(): string
    {
        return sprintf(
            'Ensuring %s "%s" %s',
            $this->type,
            $this->path,
            $this->exists ? 'exists' : 'doesn\'t exist',
        );
    }
}
