<?php

namespace Maestro2\Rector\Task;

use Maestro2\Core\Config\RepositoryNode;
use Maestro2\Core\Task\Task;

class RectorComposerUpgradeTask implements Task
{
    public function __construct(
        private string $repoPath,
        private string $phpBin = PHP_BINARY,
        private string $rectorVersion = '0.8.52'
    ) {
    }

    public function repoPath(): string
    {
        return $this->repoPath;
    }

    public function phpBin(): string
    {
        return $this->phpBin;
    }

    public function rectorVersion(): string
    {
        return $this->rectorVersion;
    }
}
