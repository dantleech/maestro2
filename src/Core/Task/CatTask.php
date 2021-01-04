<?php

namespace Maestro\Core\Task;

/**
 * Publish contents of text file
 *
 * This task is useful for debugging. If you have a README file in your
 * workspace:
 *
 * ```text:workspace/README.md
 * Hello World
 * ```
 *
 * And the task:
 *
 * ```php:task
 * new CatTask(
 *    path: 'README.md'
 * );
 * ```
 *
 * The contents will be published at level INFO.
 */
class CatTask implements Task
{
    /**
     * @param string $path Path to file in workspace
     */
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }
}
