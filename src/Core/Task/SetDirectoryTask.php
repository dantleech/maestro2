<?php

namespace Maestro\Core\Task;

/**
 * Change the workspace directory
 *
 * Use this task to change the workspace/current directory used
 * by the Filesystem service.
 *
 * The path is always absolute even if no leading `/` is provided.
 *
 * ```php
 * new SequentialTask([
 *     new SetDirectoryTask('foobar/baz'),
 *     new SetDirectoryTask('foobar/barfoo')
 * ])
 *
 * The above will change directories to `foobar/baz` and `foobar/barfoo`
 * respectively.
 */
class SetDirectoryTask implements Task
{
    /**
     * @param string $path Workspace path relative to the root.
     */
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }
}
