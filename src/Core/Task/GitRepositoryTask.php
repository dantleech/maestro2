<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Checkout a git repository
 *
 * Use this task to establish a GIT repository in your workspace.
 *
 * ```php:task
 * new GitRepositoryTask(
 *     url: 'http://example.com/my-repo',
 *     path: 'my-repo'
 * );
 * ```
 *
 * Typically you will also want to change the working directory to
 * this repository so that subsequent tasks operate on it:
 *
 * ```php:task
 * new SequentialTask([
 *     new GitRepositoryTask(url: 'http://example.com/my-repo', path: 'my-repo'),
 *     new SetDirectoryTask('my-repo')
 * ]);
 * ```
 */
class GitRepositoryTask implements Task, Stringable
{
    public function __construct(
        private string $url,
        private string $path,
        private bool $clean = true,
        private ?string $branch = null
    ) {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function __toString(): string
    {
        return sprintf('Checking out git repository "%s"', $this->url());
    }

    public function clean(): bool
    {
        return $this->clean;
    }

    public function branch(): ?string
    {
        return $this->branch;
    }
}
