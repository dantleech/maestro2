<?php

namespace Maestro\Core\Task;

use Stringable;

/**
 * Perform a GIT commit
 *
 * Run `git commit` with a specific set of paths in the current workspace
 * directory.
 *
 * ```php:task
 * new GitCommitTask(
 *     paths: [ 'src' ],
 *     message: 'Upgraded PHPUnit',
 * );
 */
class GitCommitTask implements Task, Stringable
{
    /**
     * @param list<string> $paths Paths to commit
     * @param string $message Commit message
     */
    public function __construct(
        private array $paths,
        private string $message,
        private ?string $group = null
    ) {
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return list<string>
     */
    public function paths(): array
    {
        return $this->paths;
    }

    public function group(): ?string
    {
        return $this->group;
    }

    public function __toString(): string
    {
        return sprintf('Git committing with "%s"', $this->message);
    }
}
