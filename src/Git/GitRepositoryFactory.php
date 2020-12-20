<?php

namespace Maestro\Git;

use Maestro\Core\Process\ProcessRunner;
use Maestro\Core\Vcs\Repository;
use Maestro\Core\Vcs\RepositoryFactory;
use Psr\Log\LoggerInterface;

class GitRepositoryFactory implements RepositoryFactory
{
    public function __construct(private ProcessRunner $runner, private LoggerInterface $logger)
    {
    }

    public function create(string $cwd): Repository
    {
        return new GitRepository($this->runner, $this->logger, $cwd);
    }
}
